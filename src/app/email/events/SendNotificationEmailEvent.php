<?php

namespace barrelstrength\sproutbase\app\email\events;

use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use yii\base\Event;

class SendNotificationEmailEvent extends Event
{
    public $event;

    /**
     * @var NotificationEmail
     */
    public $notificationEmail;
}
