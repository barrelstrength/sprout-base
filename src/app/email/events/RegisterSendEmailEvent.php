<?php

namespace barrelstrength\sproutbase\app\email\events;

use yii\base\Event;

class RegisterSendEmailEvent extends Event
{
    public $message;

    public $mailer;

    public $variables = [];
}