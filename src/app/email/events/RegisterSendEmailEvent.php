<?php

namespace barrelstrength\sproutbase\app\email\events;

use yii\base\Event;

/**
 * Class RegisterSendEmailEvent
 */
class RegisterSendEmailEvent extends Event
{
    public $message;
    public $mailer;
    public $variables = [];
}