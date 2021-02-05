<?php

namespace import\services;

use Generator;
use import\interfaces\FilterInterface;
use import\interfaces\ImportServiceInterface;
use import\kernel\db\QueryBuilder;
use import\kernel\phpoffice\ReadFilter;
use import\kernel\phpoffice\Row;
use import\kernel\response\ImportResponse;
use import\models\ImportCsv;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\UserException;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;

class ImportService extends Component implements ImportServiceInterface
{
    public const EVENT_DESENSITISE = 'desensitise';

    public $position;

    public $file;

    public $config;

    /**
     * @var FilterInterface
     */
    public $filter;

    /**
     * @var array
     */
    public $complement;

    public $formats;
    /**
     * @var string
     */
    public $memory = '128M';

    /**
     * @var int
     */
    public $readRows = 10000;
    /**
     * @var ActiveRecord
     */
    public $model;

    /**
     * FileParseService constructor.
     *
     * @param      $file
     * @param      $config
     * @param      $filter
     * @param int  $position
     * @param null $memory
     * @param null $complement
     *
     * @throws UserException
     */
    public function __construct($file, $config, $filter, $position = 0, $memory = null, $complement = null)
    {
        if (!$filter instanceof FilterInterface) {
            throw new UserException('未实现自定义过滤数据filter类!');
        }

        $this->file       = $file;
        $this->config     = $config;
        $this->position   = $position;
        $this->filter     = $filter;
        $model            = $this->config['model'];
        $this->model      = new $model();
        $this->memory     = $memory ?? $this->memory;
        $this->readRows   = (int)($config['read_rows'] ?? $this->readRows);
        $this->complement = $complement ?? $this->config['complement'];
        parent::__construct([]);
    }

    /**
     * @param Generator $source
     *
     * @return Generator
     * @throws UserException
     */
    public function withFormat(Generator $source): Generator
    {
        foreach ($source as $row) {
            /** @var Row $row */
            echo $row->index . PHP_EOL;
            try {
                foreach ($row->data as $attr => &$val) {
                    $val = $this->formatCellValue($attr, $val, $row);
                }
                foreach ($this->complement as $attr => $value) {
                    $row->data[$attr] = $this->formatCellValue($attr, $value, $row);
                }
            } catch (Throwable $e) {
                throw new UserException(vsprintf("ROW: %d.\nDATA: %s.\n%s", [
                    $row->index,
                    json_encode($row->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    $e,
                ]));
            }

            yield $row->data;
        }
    }

    public function import(): ImportResponse
    {
        $importResponse = new ImportResponse();

        $source = $this->withFormat($this->parseFile());
        /**
         * @var $transaction Transaction
         */
        $transaction = ImportCsv::getDb()->beginTransaction();
        $i           = $this->readRows;
        try {
            $data = [];
            while ($i-- && $row = $source->current()) {
                $source->next();
                if (!$this->filter->filter($row)) {
                    continue;
                }
                $data[] = $row;
                if (($i % 100) === 0) {
                    $importResponse->increment($this->batchWrite($data));
                    $data = [];
                }
            }
            $importResponse->increment($this->batchWrite($data));
            $transaction->commit();

            $importResponse->setIsNext((bool)$source->current());

            return $importResponse;
        } catch (Throwable $throwable) {
            $transaction->rollBack();
            throw $throwable;
        }
    }


    public function clean(): ImportResponse
    {
        $importResponse = new ImportResponse();
        $source         = $this->withFormat($this->parseFile());

        /**
         * @var $transaction Transaction
         */
        $transaction = ImportCsv::getDb()->beginTransaction();
        $i           = $this->readRows;
        try {
            while ($i-- && $row = $source->current()) {
                $source->next();
                if (!$this->filter->filter($row)) {
                    continue;
                }
                $delete = [];
                foreach ($this->config['database_columns_map'] as $key => $item) {
                    $delete[$key] = $row[$key];
                }
                $importResponse->increment($this->model::deleteAll($delete));
            }
            $transaction->commit();

            $importResponse->setIsNext((bool)$source->current());

            return $importResponse;
        } catch (Throwable $throwable) {
            $transaction->rollBack();
            throw $throwable;
        }
    }


    /**
     * @param array $header
     * @param array $config
     *
     * @return array
     */
    protected function getTemplate(array $header, array $config): array
    {
        $template = $config['template'] ?? [];
        $mapping  = [];
        foreach ($template as $attr => $column) {
            $mapping[$column][] = $attr;
        }

        $config = [];
        /** @var string $title */
        foreach ($header as $i => $title) {
            $title = trim($title);
            if (isset($mapping[$title])) {
                $config[$i] = $mapping[$title];
            }
        }

        return $config;
    }

    /**
     * @return Generator|null
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function parseFile(): Generator
    {
        ini_set('memory_limit', $this->memory);

        $startRow = $this->position * $this->readRows > 0 ? $this->position * $this->readRows : 1;
        $skipRow  = (int)($this->config['skip_rows'] ?? 0);

        $startRow     += 2 + $skipRow;
        $filterSubset = new ReadFilter($startRow, $startRow + $this->readRows);

        $reader = IOFactory::createReaderForFile($this->file);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($this->file);
        $worksheet   = $spreadsheet->getActiveSheet();

        [$row, $column] = array_values($worksheet->getHighestRowAndColumn());

        $header = [];
        foreach ($worksheet->getRowIterator(1, 1)->current()->getCellIterator('A', $column) as $cell) {
            $header[] = $cell->getValue();
        }

        $reader->setReadFilter($filterSubset);
        $worksheet->getRowIterator()->rewind();

        $template = $this->getTemplate($header, $this->config);

        foreach ($worksheet->getRowIterator($startRow, $row) as $index => $row) {
            $rowData = [];
            foreach ($row->getCellIterator('A', $column) as $k => $cell) {
                $i = Coordinate::columnIndexFromString($k) - 1;
                if (!isset($template[$i])) {
                    continue;
                }
                foreach ((array)$template[$i] as $attr) {
                    $rowData[$attr] = $cell->getValue();
                }
            }
            yield new Row($index + $startRow, $rowData);
        }
    }


    /**
     * @param string     $attribute
     * @param mixed      $value
     * @param array|null $params
     *
     * @return mixed
     */
    protected function formatCellValue(string $attribute, $value, $params = null)
    {
        $formatter = Yii::$app->getFormatter();
        if (!isset($this->formats)) {
            $this->formats = (array)ArrayHelper::getValue($this->config, 'formats');
        }
        if (empty($this->formats[$attribute])) {
            return $value;
        }
        foreach ($this->formats[$attribute] as $formatMethod) {
            if (isset($params)) {
                $formatMethod[] = $params;
            }
            $value = $formatter->format($value, $formatMethod);
        }

        return $value;
    }


    /**
     * @param array $data
     *
     * @return int
     * @throws InvalidConfigException|\yii\db\Exception|UserException
     */
    protected function batchWrite(array $data): int
    {
        if (empty($data)) {
            return 0;
        }

        $event = new Event();

        $event->data = $data;
        $this->trigger(self::EVENT_DESENSITISE, $event);

        return $this->upsert($event->data);
    }

    /**
     * @param array $data
     *
     * @return int
     * @throws InvalidConfigException
     * @throws \yii\db\Exception|UserException
     */
    protected function upsert(array $data): int
    {
        if (empty($data)) {
            return 0;
        }

        if (!$this->model instanceof Model) {
            throw new UserException('模型未定义!');
        }
        /**
         * @var  Connection $db
         */
        $db = $this->model::getDb();


        $table = $this->model::tableName();

        $columnNames = $this->model::getTableSchema()->getColumnNames();

        $columns = array_intersect(array_keys(current($data)), $columnNames);

        $db->enableLogging   = false;
        $db->enableProfiling = false;

        $count = 0;
        $query = new QueryBuilder($db);

        foreach ($data as $item) {
            $row[] = $item;
            $sql   = $query->insertDuplicate($table, $columns, $row);
            if ($db->createCommand($sql)->execute()) {
                ++$count;
            }
        }

        return $count;
    }

}


