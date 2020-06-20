<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\email\events\notificationevents;

use barrelstrength\sproutbase\app\email\base\NotificationEvent;
use Craft;
use craft\events\UserEvent;
use craft\records\User as UserRecord;
use yii\web\User;

class UsersLogin extends NotificationEvent
{
    public function getEventClassName()
    {
        return User::class;
    }

    public function getEventName()
    {
        return User::EVENT_AFTER_LOGIN;
    }

    public function getEventHandlerClassName()
    {
        return UserEvent::class;
    }

    public function getName(): string
    {
        return Craft::t('sprout', 'When a user is logged in.');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Triggered when a user is logged in.');
    }

    public function getEventObject()
    {
        return $this->event->user;
    }

    public function getMockEventObject()
    {
        $criteria = UserRecord::find();

        return $criteria->one();
    }
}
