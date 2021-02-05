<?php

namespace import\examples\oss;

use common\models\KeyValue;
use import\models\ImportCsv;
use Qcloud\Cos\Client;
use yii\base\UserException;
use yii\di\Instance;
use Yii;

class QCloudAdapter
{
    /**
     * @var string
     */
    protected const TMP_FILE_DIR = '/tmp/qcloud/';

    public function upload(ImportCsv $csv): array
    {
        /** @var Client $qCloudClient */
        $qCloudClient = Instance::ensure('QCloudCosV5', Client::class);
        $bucket       = KeyValue::takeAsObject('q_cloud_config_for_external')->bucket ?? 'bizfiles';

        $body = fopen($csv->file->tempName, 'rb');

        if (!$qCloudClient->doesObjectExist($bucket, $csv->object)) {
            $result = $qCloudClient->Upload($bucket, $csv->object, $body);
            Yii::trace((string)$result, __METHOD__);
        }

        $signUrl = $qCloudClient->getObjectUrl($bucket, $csv->object, '+10 minute');

        $url = strtok($signUrl, '?');
        Yii::trace($signUrl, __METHOD__);

        return [
            'code' => 0,
            'msg'  => '上传成功',
            'data' => [
                'url' => $url,
            ],
        ];
    }

    public function download(ImportCsv $csv): array
    {
        try {
            /** @var Client $qCloudClient */
            $qCloudClient = Instance::ensure('QCloudCosV5', Client::class);
            $bucket       = KeyValue::takeAsObject('q_cloud_config_for_external')->bucket ?? 'bizfiles';
            $filename     = self::TMP_FILE_DIR . $csv->file;
            //TODO 解决文件创建的问题
            if (!is_dir(self::TMP_FILE_DIR)
                && !mkdir($concurrentDirectory = self::TMP_FILE_DIR, 0777, true)
                && !is_dir($concurrentDirectory)
            ) {
                throw new UserException(sprintf('目录"%s"创建失败.', $concurrentDirectory));
            }
            fopen($filename, 'wb');
            $result =
                $qCloudClient->getObject(['Bucket' => $bucket, 'Key' => $csv->oss_file_name, 'SaveAs' => $filename]);

            return [
                'code' => 0,
                'msg'  => 'ok',
                'data' => [
                    'file' =>
                        $filename
                        // dirname(__DIR__) . '/../cashfree_yomoyo1充值对账单1.csv'
                        ?? null,
                ],
            ];
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function delete(ImportCsv $csv): array
    {
        try {
            /** @var Client $qCloudClient */
            $qCloudClient = Instance::ensure('QCloudCosV5', Client::class);
            $bucket       = KeyValue::takeAsObject('q_cloud_config_for_external')->bucket ?? 'bizfiles';

            $ret = $qCloudClient->deleteObject(['Bucket' => $bucket, 'Key' => $csv->oss_file_name]);

            return ['code' => 0, 'msg' => 'ok', 'data' => null];
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}


