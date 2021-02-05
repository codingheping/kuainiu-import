<?php

namespace import\interfaces;

interface FilterInterface
{
    public function filter(array $row): bool;
}


