<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\email\events\notificationevents;

use barrelstrength\sproutbase\app\email\base\NotificationEvent;
use Craft;
use craft\base\Element;
use craft\elements\Entry;
use craft\helpers\ElementHelper;
use yii\base\Event;

class EntriesDelete extends NotificationEvent
{
    public function getEventClassName()
    {
        return Entry::class;
    }

    public function getEventName()
    {
        return Entry::EVENT_AFTER_DELETE;
    }

    public function getEventHandlerClassName()
    {
        return Event::class;
    }

    public function getName(): string
    {
        return Craft::t('sprout', 'When an entry is deleted');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Triggered when an entry is deleted.');
    }

    public function getEventObject()
    {
        $event = $this->event ?? null;

        return $event->sender ?? null;
    }

    public function getMockEventObject()
    {
        $criteria = Entry::find();

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

        /** @var Element $element */
        $element = $event->sender;

        if (get_class($element) !== Entry::class) {
            $this->addError('event', Craft::t('sprout', 'Event Element does not match craft\elements\Entry class.'));
        }

        if (ElementHelper::isDraftOrRevision($element)) {
            $this->addError('event', Craft::t('sprout', 'Event Element is a draft or revision.'));
        }
    }
}
