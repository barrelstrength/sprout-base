<?php

namespace barrelstrength\sproutbase\app\email\base;

use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use craft\base\SavableComponent;
use yii\base\Event;

/**
 *
 * @property null|string $eventHandlerClassName
 * @property null|string $eventId
 * @property null|mixed $mockEventObject
 * @property null|mixed $eventObject
 * @property string $name
 * @property null|string $eventName
 * @property string $description
 * @property string $settingsHtml
 * @property null|string $eventClassName
 */
abstract class NotificationEvent extends SavableComponent
{
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
     * @return string|null
     * @example Event::on($class, $name, function($handler) { ... });
     *
     * @see     \yii\base\Event
     */
    abstract public function getEventClassName();

    /**
     * Returns the event name.
     *
     * This value is used for the Event::on $name parameter
     *
     * @return string|null
     * @example Event::on($class, $name, function($handler) { ... });
     *
     * @see     \yii\base\Event
     */
    abstract public function getEventName();

    /**
     * Returns the callable event handler.
     *
     * This value is used for the Event::on $handler parameter
     *
     * @return string|null
     * @example Event::on($class, $name, function($handler) { ... });
     *
     * @see     \yii\base\Event
     */
    abstract public function getEventHandlerClassName();

    /**
     * Returns the name of the event
     *
     * @return string
     * @example
     *
     * - When an Entry is saved
     * - When a User is activated
     * - When a Sprout Forms Entry is saved
     *
     */
    abstract public function getName(): string;

    /**
     * Returns a short description of this event
     *
     * @return string
     * @example Triggers when an entry is saved
     *
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * Returns a rendered html string to use for capturing user input
     *
     * @return string
     * @example
     * <h3>Select Sections</h3>
     * <p>Please select what Sections should trigger the save entry event</p>
     * <input type="checkbox" id="sectionIds[]" value="1">
     * <input type="checkbox" id="sectionsIds[]" value="2">
     *
     */
    public function getSettingsHtml(): string
    {
        return '';
    }

    /**
     * Returns the object that represents the event. The object returned will be passed to renderObjectTemplate
     * and be available to output in the Notification Email templates via Craft Object Syntax:
     *
     * @return mixed
     * @example   - Usage in Notification Email Templates
     *            If getEventObject returns a craft\elements\Entry model, the Notification Email Templates
     *            can output data from that model such as {title} OR {{ object.title }}
     *
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
     * @return void
     */
    public function validateEvent()
    {
        return null;
    }
}
