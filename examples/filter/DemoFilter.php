<?php

namespace import\examples\filter;

use import\interfaces\FilterInterface;


class DemoFilter implements FilterInterface
{
    public function filter(array $row): bool
    {
        // 只导入成功和失败数据
        if (in_array(($row['channel_reconci_status'] ?? 0), [2, 3], true)) {
            return true;
        }

        return false;
    }
}


