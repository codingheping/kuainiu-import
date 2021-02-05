<?php

namespace import\kernel\phpoffice;

class Row
{
    /**
     * @var int 文件中的行数
     */
    public $index;

    /**
     * @var array 文件中的一行数据
     */
    public $data;

    public function __construct($index, $row)
    {
        $this->index = $index;
        $this->data  = $row;
    }
}
