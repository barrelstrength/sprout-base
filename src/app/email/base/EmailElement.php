<?php

namespace barrelstrength\sproutbase\app\email\base;

use craft\base\Element;
use Craft;

abstract class EmailElement extends Element
{
    /**
     * The Subject Line of your email. Your title will also default to the Subject Line unless you set a Title Format.
     *
     * @var string
     */
    public $subjectLine;

    /**
     * A comma, delimited list of recipients
     *
     * @var string
     */
    public $recipients;

    /**
     * List settings.
     *
     * List settings HTML is provided by a List integration. Values will be saved in JSON format and will be processed by the active mailer and list integration.
     *
     * @var string
     */
    public $listSettings;

    /**
     * The sender name
     *
     * @var string
     */
    public $fromName;

    /**
     * The sender email
     *
     * @var string
     */
    public $fromEmail;

    /**
     * The sender replyTo email, if different than the sender email
     *
     * @var string
     */
    public $replyToEmail;

    /**
     * Enable or disable file attachments when notification emails are sent.
     *
     * If disabled, files will still be stored in Craft after form submission. This only determines if they should also be sent via email.
     *
     * @var bool
     */
    public $enableFileAttachments;

    /**
     * @var bool
     */
    private $_isTest = false;

    /**
     * @var object|null
     */
    private $_eventObject = null;

    /**
     * Returns whether this should be treated as a Test Email
     *
     * @return bool
     */
    public function getIsTest(): bool
    {
        return $this->_isTest;
    }

    /**
     * Sets whether this should be treated as a Test Email
     *
     * @param bool $value
     */
    public function setIsTest($value = true)
    {
        $this->_isTest = $value;
    }

    /**
     * Sets a Notification Event object
     *
     * @param object|null $value
     */
    public function setEventObject($value = null)
    {
        $this->_eventObject = $value;
    }

    /**
     * Returns the Notification Event object
     *
     * @return object|null
     */
    public function getEventObject()
    {
        return $this->_eventObject;
    }

    /**
     * Returns the Email Template ID for the given Email Element
     *
     * @return int
     */
    abstract public function getEmailTemplateId();

    /**
     * @return array
     */
    public function getFieldLayoutTabs()
    {
        $tabs = [];

        if ($this->getFieldLayout() !== null) {
            $fieldLayoutTabs = $this->getFieldLayout()->getTabs();

            if (!empty($fieldLayoutTabs)) {
                foreach ($fieldLayoutTabs as $index => $tab) {
                    // Do any of the fields on this tab have errors?
                    $hasErrors = false;

                    if ($this->hasErrors()) {
                        foreach ($tab->getFields() as $field) {
                            if ($this->getErrors($field->handle)) {
                                $hasErrors = true;
                                break;
                            }
                        }
                    }

                    $tabs[] = [
                        'label' => Craft::t('sprout-base', $tab->name),
                        'url' => '#tab'.($index + 1),
                        'class' => $hasErrors ? 'error' : null
                    ];
                }
            }
        }

        return $tabs;
    }
}