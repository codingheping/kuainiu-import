<?php

namespace import\interfaces;

use import\kernel\response\ImportResponse;

interface ImportServiceInterface
{
    public function import(): ImportResponse;

    public function clean(): ImportResponse;
}


