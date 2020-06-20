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


/**
 *
 * @property null $eventHandlerClassName
 * @property mixed $mockEventObject
 * @property null $eventObject
 * @property mixed $name
 * @property mixed $eventName
 * @property mixed $description
 * @property string $eventClassName
 */
class UsersDelete extends NotificationEvent
{
    public $whenNew = false;

    public $whenUpdated = false;

    public $adminUsers = false;

    public function getEventClassName()
    {
        return User::class;
    }

    public function getEventName()
    {
        return User::EVENT_AFTER_DELETE;
    }

    public function getEventHandlerClassName()
    {
        return null;
    }

    public function getName(): string
    {
        return Craft::t('sprout', 'When a user is deleted');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Triggered when a user is deleted.');
    }

    public function getEventObject()
    {
        $event = $this->event ?? null;

        return $event->sender ?? null;
    }

    public function getMockEventObject()
    {
        $criteria = User::find();

        return $criteria->one();
    }

    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['event'], 'validateEvent'];

        return $rules;
    }

    public function validateEvent()
    {
        $event = $this->event ?? null;

        if (!$event) {
            $this->addError('event', Craft::t('sprout', 'ElementEvent does not exist.'));
        }

        if (get_class($event->sender) !== User::class) {
            $this->addError('event', Craft::t('sprout', 'Event Element does not match craft\elements\Entry class.'));
        }
    }
}
