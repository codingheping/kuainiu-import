<?php

namespace import\examples\task;

use common\business\tasks\TaskHandler;
use common\models\Task;
use import\models\ImportCsv;
use import\services\ImportService;
use Yii;
use yii\base\UserException;

class ImportCsvTaskHandler extends TaskHandler
{

    public function process(Task $task): array
    {
        $request = json_decode($task->task_request_data, false);
        $csv     = $this->find($request->dataSourceId);
        $config  = ImportCsv::config()[$request->type] ?? [];
        $file    = $csv->download();
        $filter  = Yii::$app->get('filter');

        $importService  = new ImportService($file, $config, $filter, $request->position);
        $importResponse = $importService->import();

        $csv->success_line += $importResponse->getSuccessLine();
        $csv->status       = ImportCsv::SUCCESS_STATUS;

        if ($importResponse->isNext()) {
            ++$request->position;
            self::make($request);
            $csv->status = ImportCsv::ING_STATUS;
        }

        if (!$csv->save(false)) {
            throw new UserException(json_encode($csv->getErrors()));
        }

        return [
            'code'    => '0',
            'message' => '执行成功',
            'data'    => $importResponse->getSuccessLine(),
        ];
    }

    protected function find(int $id): ImportCsv
    {
        $csv = ImportCsv::findOne($id);
        if (!$csv) {
            throw new UserException('数据不存在!');
        }

        return $csv;
    }
}
