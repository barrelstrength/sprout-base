<?php

namespace barrelstrength\sproutbase\contracts\sproutemail;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use yii\base\Event;

/**
 * The official API for dynamic event registration and handling
 *
 * Class SproutEmailBaseEvent
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
    public function getEventId()
    {
        return strtolower(str_replace('\\', '-', get_class($this)));
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
    abstract public function getEventName();

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
     * @param $options
     */
    final public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Returns the qualified event name to use when registering with craft()->on
     *
     * @example entries.saveEntry
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Returns the event title to use when displaying a label or similar use case
     *
     * @example Craft Save Entry
     *
     * @return string
     */
    public function getTitle()
    {
        return null;
    }

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
     * <p>Please select what sections you want the save entry event to trigger on</p>
     * <input type="checkbox" id="sectionIds[]" value="1">
     * <input type="checkbox" id="sectionsIds[]" value="2">
     *
     * @return string
     */
    public function getSettingsHtml()
    {
        return '—';
    }

    /**
     * Returns the value that should be saved to options for the notification (registered event)
     *
     * @example
     * return craft()->request->getPost('sectionIds');
     *
     * @return mixed
     */
    public function prepareOptions()
    {
        return [];
    }

    /**
     * Returns whether the campaign entry options are valid for this model
     *
     * @example
     * Let $options be an array containing section ids (1,3)
     * Let $model be an EntryModel with section id (1)
     * Let $params be the entry.saveEntry event params
     * Result is true
     *
     * @note
     * This is used when determining whether a campaign should be sent
     *
     * @param mixed $options
     * @param mixed $eventData Usually whatever prepareParams() returns in its value key
     * @param array $params
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

    /**
     * Returns the data passed in by the triggered event
     *
     * @example
     * return $event->params['entry'];
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
     * Gives the event a chance to attach the value to the right field id before outputting it
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
     * Gives the event the ability to let a mailer test sending notifications with mocked params
     *
     * @return array
     */
    public function getMockedParams()
    {
        return [];
    }
}
