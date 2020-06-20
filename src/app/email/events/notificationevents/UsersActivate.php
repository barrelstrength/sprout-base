<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\email\events\notificationevents;

use barrelstrength\sproutbase\app\email\base\NotificationEvent;
use Craft;
use craft\elements\User;
use craft\events\UserEvent;
use craft\services\Users;

class UsersActivate extends NotificationEvent
{
    public function getEventClassName()
    {
        return Users::class;
    }

    public function getEventName()
    {
        return Users::EVENT_AFTER_ACTIVATE_USER;
    }

    public function getEventHandlerClassName()
    {
        return UserEvent::class;
    }

    public function getName(): string
    {
        return Craft::t('sprout', 'When a user is activated');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Triggered when a user is activated.');
    }

    public function getEventObject()
    {
        return $this->event->user;
    }

    public function getMockEventObject()
    {
        $criteria = User::find();

        return $criteria->one();
    }
}
