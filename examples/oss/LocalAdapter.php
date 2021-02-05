<?php

namespace import\examples\oss;


use import\adapter\AbstractAdapter;
use import\models\ImportCsv;

class LocalAdapter extends AbstractAdapter
{

    public function upload(ImportCsv $csv): array
    {
        return ['code' => 0, 'msg' => 'ok', 'data' => ['url' => '']];
    }

    public function download(ImportCsv $csv): array
    {
        return ['code' => 0, 'msg' => 'ok', 'data' => ['url' => '']];
    }

    public function delete(ImportCsv $csv): array
    {
        return ['code' => 0, 'msg' => 'ok', 'data' => ['download_url' => '']];
    }
}


