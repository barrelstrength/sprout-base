<?php

namespace barrelstrength\sproutbase\sproutemail\events;

use yii\base\Event;

class RegisterMailersEvent extends Event
{
    public $mailers = [];
}