<?php

namespace barrelstrength\sproutbase\contracts\sproutemail;

use yii\base\Event;

/**
 * The official API for dynamic event registration and handling
 *
 * Class SproutEmailBaseEvent
 *
 * @package Craft
 */
class BaseEvent
{
    /**
     * @var array|null
     */
    protected $options;

    /**
     * @var string
     */
    protected $pluginName;

    /**
     * @param $pluginName
     */
    public function setPluginName($pluginName)
    {
        $this->pluginName = $pluginName;
    }

    /**
     * Returns the event title when used in string context
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * Returns the string with namespace that is for html attribute class compatibility
     *
     * @return string
     */
    public function getEventId()
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
     * Returns the qualified event name to use when registering with craft()->on
     *
     * @example entries.saveEntry
     *
     * @return string
     */
    public function getName()
    {
        return $this->getEventId();
    }

    /**
     * Returns the event title to use when displaying a label or similar use case
     *
     * @example Craft Save Entry
     *
     * @return string
     */
    public function getTitle()
    {
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
    public function getOptionsHtml()
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
