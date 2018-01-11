<?php

namespace barrelstrength\sproutbase\events;

use yii\base\Event;

class RegisterMailersEvent extends Event
{
    public $mailers = [];
}