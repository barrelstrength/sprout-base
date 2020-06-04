<?php

namespace barrelstrength\sproutbase\app\email\events;

use yii\base\Event;

class RegisterMailersEvent extends Event
{
    public $mailers = [];
}