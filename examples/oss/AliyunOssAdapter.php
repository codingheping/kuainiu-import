<?php

namespace import\examples\oss;

use common\models\KeyValue;
use import\models\ImportCsv;

class AliyunOssAdapter
{

    public function upload(ImportCsv $csv): array
    {
        $config = KeyValue::takeAsArray('oss_config');
    }

    public function download(ImportCsv $csv): array
    {
        // TODO: Implement download() method.
    }

    public function delete(ImportCsv $csv): array
    {
        // TODO: Implement delete() method.
    }
}


