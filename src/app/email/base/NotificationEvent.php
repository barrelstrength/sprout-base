<?php

namespace barrelstrength\sproutbase\app\email\base;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use craft\base\SavableComponent;


use yii\base\Event;

/**
 * The Notification Email Event API
 *
 * Class NotificationEvent
 *
 * @package Craft
 *
 * @property string $eventHandlerClassName
 * @property string $eventId
 * @property mixed  $mockEventObject
 * @property mixed  $eventObject
 * @property string $name
 * @property string $eventName
 * @property string $description
 * @property string $settingsHtml
 * @property string $eventClassName
 */
abstract class NotificationEvent extends SavableComponent
{
    use BaseSproutTrait;

    /**
     * @var NotificationEmail $notificationEmail
     */
    public $notificationEmail;

    /**
     * @var Event $event
     */
    public $event;

    /**
     * Returns the event title when used in string context
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Returns the namespace as a string with dashes so it can be used in html as a css class
     *
     * @return string|null
     */
    final public function getEventId()
    {
        return strtolower(str_replace('\\', '-', get_class($this)));
    }

    /**
     * Returns the fully qualified class name to which the event handler needs to attach.
     *
     * This value is used for the Event::on $class parameter
     *
     * @see     \yii\base\Event
     * @example Event::on($class, $name, function($handler) { ... });
     *
     * @return string|null
     */
    abstract public function getEventClassName();

    /**
     * Returns the event name.
     *
     * This value is used for the Event::on $name parameter
     *
     * @see     \yii\base\Event
     * @example Event::on($class, $name, function($handler) { ... });
     *
     * @return string|null
     */
    abstract public function getEventName();

    /**
     * Returns the callable event handler.
     *
     * This value is used for the Event::on $handler parameter
     *
     * @see     \yii\base\Event
     * @example Event::on($class, $name, function($handler) { ... });
     *
     * @return string|null
     */
    abstract public function getEventHandlerClassName();

    /**
     * Returns the name of the event
     *
     * @example
     *
     * - When an Entry is saved
     * - When a User is activated
     * - When a Sprout Forms Entry is saved
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Returns a short description of this event
     *
     * @example Triggers when an entry is saved
     *
     * @return string
     */
    public function getDescription(): string
    {
        return null;
    }

    /**
     * Returns a rendered html string to use for capturing user input
     *
     * @example
     * <h3>Select Sections</h3>
     * <p>Please select what Sections should trigger the save entry event</p>
     * <input type="checkbox" id="sectionIds[]" value="1">
     * <input type="checkbox" id="sectionsIds[]" value="2">
     *
     * @return string
     */
    public function getSettingsHtml(): string
    {
        return '';
    }

    /**
     * Returns the object that represents the event. The object returned will be passed to renderObjectTemplate
     * and be available to output in the Notification Email templates via Craft Object Syntax:
     *
     * @example - Usage in Notification Email Templates
     *            If getEventObject returns a craft\elements\Entry model, the Notification Email Templates
     *            can output data from that model such as {title} OR {{ object.title }}
     *
     * @return mixed
     */
    public function getEventObject()
    {
        return null;
    }


    /**
     * Returns mock data for $event->params that will be used when sending test Notification Emails.
     *
     * Real data can be dynamically retrieved from your database or a static fallback can be provided.
     *
     * @return mixed
     */
    public function getMockEventObject()
    {
        return null;
    }

    /**
     * Additional validation for triggering events.
     *
     * @return bool
     */
    public function validateEvent(): bool
    {
        return null;
    }
}
