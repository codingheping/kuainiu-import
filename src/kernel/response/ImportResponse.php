<?php

namespace import\kernel\response;


class ImportResponse
{
    /**
     * 文件资源迭代器
     *
     * @var array|null
     */
    protected $iterator;

    /**
     * 是否继续下次循环
     *
     * @var bool
     */
    protected $isNext = false;

    /**
     * 导入|清理 成功行数
     *
     * @var int
     */
    protected $successLine = 0;


    /**
     * @return array
     */
    public function getIterator(): ?array
    {
        return $this->iterator;
    }


    /**
     * @return bool
     */
    public function isNext(): bool
    {
        return $this->isNext;
    }

    /**
     * @param array|null $iterator
     */
    public function setIterator(iterable $iterator): void
    {
        $this->iterator = $iterator;
    }

    /**
     * @param bool $isNext
     */
    public function setIsNext(bool $isNext): void
    {
        $this->isNext = $isNext;
    }

    /**
     * @param int $successLine
     */
    public function setSuccessLine(int $successLine): void
    {
        $this->successLine = $successLine;
    }

    /**
     * @return int
     */
    public function getSuccessLine(): int
    {
        return $this->successLine;
    }

    /**
     * @param int $num
     */
    public function increment(int $num): void
    {
        $this->successLine += $num;
    }

    /**
     * @param int $num
     */
    public function subtract(int $num): void
    {
        $this->successLine -= $num;
    }

}


