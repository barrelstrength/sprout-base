<?php

namespace barrelstrength\sproutbase\contracts\sproutemail;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use yii\base\Event;

/**
 * The Notification Email Event API
 *
 * Class BaseEvent
 *
 * @package Craft
 */
abstract class BaseEvent
{
    use BaseSproutTrait;

    /**
     * @var array|null
     */
    protected $options;

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
     * @return string
     */
    final public function getEventId()
    {
        return strtolower(str_replace('\\', '-', get_class($this)));
    }

    /**
     * @param $options
     */
    final public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Returns the fully qualified class name to which the event handler needs to attach.
     *
     * This value is used for the Event::on $class parameter
     *
     * @see \yii\base\Event
     * @example Event::on($class, $name, function($handler) { ... });
     *
     * @return string
     */
    abstract public function getEventClassName();

    /**
     * Returns the event name.
     *
     * This value is used for the Event::on $name parameter
     *
     * @see \yii\base\Event
     * @example Event::on($class, $name, function($handler) { ... });
     *
     * @return string
     */
    abstract public function getEvent();

    /**
     * Returns the callable event handler.
     *
     * This value is used for the Event::on $handler parameter
     *
     * @see \yii\base\Event
     * @example Event::on($class, $name, function($handler) { ... });
     *
     * @return string
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
    abstract public function getName();

    /**
     * Returns a short description of this event
     *
     * @example Triggers when an entry is saved
     *
     * @return string
     */
    public function getDescription()
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
    public function getSettingsHtml()
    {
        return '';
    }

    /**
     * Returns the value that should be saved to the sproutemail_notificationemails.options column
     * for the Notification Email's selected Event
     *
     * @example
     * return Craft::$app->getRequest()->getBodyParm('sectionIds');
     *
     * @return mixed
     */
    public function prepareOptions()
    {
        return [];
    }

    /**
     * Prepare the event parameters to be used in the dynamic event
     *
     * $event->params provides the value that can be used in the validateOptions method.
     * $event->params['value'] should be the value of the $element object for the specific event
     *
     * @example
     * return $event->params['value'] = $element;
     *
     * @param Event $event
     *
     * @return mixed
     */
    public function prepareParams(Event $event)
    {
        return $event->params;
    }

    /**
     * Returns mock data for $event->params that will be used when sending test Notification Emails.
     *
     * Real data can be dynamically retrieved from your database or a static fallback can be provided.
     *
     * @return array
     */
    public function getMockedParams()
    {
        return [];
    }

    /**
     * Gives the event a chance to modify the value stored in the sproutemail_notificationemails.options
     * column before displaying it as settings to the user
     *
     * @param $value
     *
     * @return mixed
     */
    public function prepareValue($value)
    {
        return $value;
    }

    /**
     * Determines if an event matches the conditions defined in it's settings for a Notification Email.
     *
     * If the Notification Email Event options validate, the Notification Email will be triggered
     * If the Notification Email Event options don't validate, no message will be triggered
     *
     * @example
     * Let $options be an array containing section ids (1,3)
     * Let $model be an EntryModel with section id (1)
     * Let $params be the EVENT_AFTER_SAVE_ELEMENT event params
     * Result is true
     *
     * @todo - revisit if we need both $eventData and $params as separate variables or can just pass $params
     *
     * @param mixed $options
     * @param mixed $eventData $event->params['value']
     * @param array $params    $event->params
     *
     * @note
     * $eventData will be an element model most of the time but...
     * it could also be a string as is the case for user session login
     *
     * @return bool
     */
    public function validateOptions($options, $eventData, array $params = [])
    {
        return true;
    }
}
