<?php

namespace import\examples\listener;

use common\models\KeyValue;
use import\event\OssEvent;
use import\examples\task\CleanCsvTaskHandler;
use import\examples\task\ImportCsvTaskHandler;
use import\models\BizOssFile;
use import\models\ImportCsv;
use kvmanager\KVException;
use Qcloud\Cos\Client;
use yii\base\Event;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\UserException;
use yii\di\Instance;
use Yii;

class OssListener
{
    /**
     * @var Event
     */
    public $event;

    public function __construct()
    {
    }

    /**
     * @param Event|OssEvent $event
     *
     * @throws \Exception
     */
    public function process($event): void
    {
        /**
         * @var ImportCsv $model
         */
        $model = $event->sender;

        if ($model->status === ImportCsv::CLEAN_STATUS && $event->name === BizOssFile::EVENT_AFTER_UPDATE) {
            $this->clean($model);
        }
        if ($event->name === BizOssFile::EVENT_AFTER_INSERT
            || ($model->status === ImportCsv::REBUILD_STATUS
                && $event->name === BizOssFile::EVENT_AFTER_UPDATE)
        ) {
            $this->import($model);
        }
        if ($event->name === BizOssFile::EVENT_AFTER_DELETE) {
            $this->delete($model);
        }
        if ($event->name === BizOssFile::EVENT_BEFORE_INSERT) {
            $this->upload($event, $model);
        }
        if ($event->name === BizOssFile::EVENT_DOWNLOAD) {
            $this->download($event, $model);
        }
    }

    /**
     * 清理
     * @param ImportCsv $csv
     *
     * @throws Exception
     * @throws UserException
     */
    protected function clean(ImportCsv $csv): void
    {
        CleanCsvTaskHandler::make([
            'dataSourceId' => $csv->id,
            'read_rows'    => ImportCsv::config()[$csv->type]['read_rows'],
            'position'     => 0,
            'type'         => $csv->type,
            'filter'       => ImportCsv::config()[$csv->type]['filter'],
        ]);
    }

    /**
     * 导入
     * @param ImportCsv $csv
     *
     * @throws Exception
     * @throws UserException
     */
    protected function import(ImportCsv $csv): void
    {
        ImportCsvTaskHandler::make([
            'dataSourceId' => $csv->id,
            'read_rows'    => ImportCsv::config()[$csv->type]['read_rows'],
            'position'     => 0,
            'type'         => $csv->type,
            'filter'       => ImportCsv::config()[$csv->type]['filter'],
        ]);
    }

    /**
     * 删除
     * @param ImportCsv $csv
     *
     * @throws KVException
     * @throws InvalidConfigException
     */
    protected function delete(ImportCsv $csv): void
    {
        try {
            /** @var Client $qCloudClient */
            $qCloudClient = Instance::ensure('QCloudCosV5', Client::class);
            $bucket       =
                KeyValue::takeAsObject('q_cloud_config_for_external')->bucket ??
                'bizfiles';

            $qCloudClient->deleteObject(['Bucket' => $bucket, 'Key' => $csv->oss_file_name]);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }


    /**
     * 下载
     * @param OssEvent  $event
     * @param ImportCsv $csv
     *
     * @throws InvalidConfigException
     * @throws KVException
     */
    protected function download(OssEvent $event, ImportCsv $csv): void
    {
        try {
            /** @var Client $qCloudClient */
            $qCloudClient = Instance::ensure('QCloudCosV5', Client::class);
            $bucket       =
                KeyValue::takeAsObject('q_cloud_config_for_external')->bucket ??
                'bizfiles';
            $filename     = '/tmp/qcloud/' . $csv->file;
            fopen($filename, 'wb');
            $qCloudClient->getObject([
                'Bucket' => $bucket,
                'Key'    => $csv->oss_file_name,
                'SaveAs' => $filename,
            ]);
            $event->file = $filename;
            $event->name = $csv->file;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 上传
     * @param           $event
     * @param ImportCsv $csv
     *
     * @throws InvalidConfigException
     * @throws KVException
     */
    protected function upload($event, ImportCsv $csv): void
    {
        /** @var Client $qCloudClient */
        $qCloudClient = Instance::ensure('QCloudCosV5', Client::class);
        $bucket       =
            KeyValue::takeAsObject('q_cloud_config_for_external')->bucket ?? 'bizfiles';

        $body = fopen($csv->file->tempName, 'rb');

        if (!$qCloudClient->doesObjectExist($bucket, $csv->oss_file_name)) {
            $result = $qCloudClient->Upload($bucket, $csv->oss_file_name, $body);
            Yii::trace((string)$result, __METHOD__);
        }

        $signUrl = $qCloudClient->getObjectUrl($bucket, $csv->oss_file_name, '+10 minute');

        $url = strtok($signUrl, '?');

        $csv->domain_url  = $url;
        $csv->file        = $csv->file->name;
        $csv->from_system = 'biz';
        $event->isValid   = true;
    }
}
