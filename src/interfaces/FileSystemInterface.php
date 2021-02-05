<?php

namespace import\interfaces;

use import\models\ImportCsv;

interface FileSystemInterface
{
    /**
     * @param ImportCsv $csv
     *
     * @return array
     * 返回值
     * @example Results: <<<<
     *           ['code' => 0,'msg'  => '上传成功','data' => ['url' => $url]];
     *          >>>
     */
    public function upload(ImportCsv $csv): array;

    /**
     * @param ImportCsv $csv
     *
     * @return array
     * 返回值
     * @example Results: <<<<<
     *          ['code' => 0,'msg'  => 'ok','data' => ['file' =>$filenam ?? null],];
     *          >>>>>
     */
    public function download(ImportCsv $csv): array;

    /**
     * @param ImportCsv $csv
     *
     * @return array
     * 返回值
     * @example Results: <<<<<
     *          ['code' => 0, 'msg' => 'ok', 'data' => null];
     *          >>>>>
     */
    public function delete(ImportCsv $csv): array;
}

