<?php

namespace import\event;

use yii\base\Event;

class OssEvent extends Event
{
    public $file = null;

    public $name = null;
}

