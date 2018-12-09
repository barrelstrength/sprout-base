<?php

namespace barrelstrength\sproutbase\app\email\base;

use barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates;
use barrelstrength\sproutbase\app\email\emailtemplates\CustomTemplates;
use barrelstrength\sproutbase\app\email\mailers\DefaultMailer;
use barrelstrength\sproutemail\models\Settings;
use craft\base\Element;
use Craft;
use craft\base\Field;

/**
 *
 * @property bool                                                                                                                                                                                            $isTest
 * @property null|object                                                                                                                                                                                     $eventObject
 * @property int                                                                                                                                                                                             $emailTemplateId
 * @property \barrelstrength\sproutbase\app\email\base\Mailer                                                                                                                                                $mailer
 * @property array                                                                                                                                                                                           $fieldLayoutTabs
 * @property \barrelstrength\sproutbase\app\email\base\EmailTemplates|\barrelstrength\sproutbase\app\email\emailtemplates\CustomTemplates|\barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates $emailTemplates
 */
abstract class EmailElement extends Element
{
    // Constants
    // =========================================================================

    const ENABLED = 'enabled';
    const PENDING = 'pending';
    const DISABLED = 'disabled';
    /**
     * The Subject Line of your email. Your title will also default to the Subject Line unless you set a Title Format.
     *
     * @var string
     */
    public $subjectLine;

    /**
     * A comma, delimited list of recipients (To email)
     *
     * @var string
     */
    public $recipients;

    /**
     * A comma, delimited list of cc emails
     *
     * @var string
     */
    public $cc;

    /**
     * A comma, delimited list of bcc emails
     *
     * @var string
     */
    public $bcc;

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
    private $_eventObject;

    /**
     * The Email Templates model after $templateId is processed
     *
     * @var EmailTemplates|null
     */
    private $_emailTemplates;

    /**
     * @var boolean
     */
    public $singleEmail;

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
    abstract public function getEmailTemplateId(): int;

    /**
     * @param EmailTemplates $emailTemplates
     */
    public function setEmailTemplates($emailTemplates)
    {
        $this->_emailTemplates = $emailTemplates;
    }

    /**
     * @return EmailTemplates|BasicTemplates|CustomTemplates
     * @throws \yii\base\Exception
     */
    public function getEmailTemplates()
    {
        // If we've already figured out which EmailTemplates to use, use them
        if ($this->_emailTemplates !== null) {

            /**
             * Make sure we have the latest EmailElement
             *
             * @var $emailTemplates EmailTemplates
             */
            $emailTemplates = $this->_emailTemplates;
            $emailTemplates->email = $this;

            return $emailTemplates;
        }

        // Set our default
        $emailTemplates = new BasicTemplates();

        $sproutEmail = Craft::$app->plugins->getPlugin('sprout-email');
        $sitePath = Craft::$app->path->getSiteTemplatesPath();

        // Allow our settings to override our default
        if ($sproutEmail) {
            /**
             * @var Settings $settings
             */
            $settings = $sproutEmail->getSettings();

            if ($settings->emailTemplateId) {

                if ($settings->emailTemplateId instanceof EmailTemplates) {
                    $emailTemplates = new $settings->emailTemplateId();
                } else {
                    // custom folder on site path
                    $templatePath = $sitePath.DIRECTORY_SEPARATOR.$settings->emailTemplateId;

                    $emailTemplates = new CustomTemplates();
                    $emailTemplates->setPath($templatePath);
                }
            }
        }

        $isCustom = false;
        $emailTemplateId = $this->getEmailTemplateId() ?? null;

        // Allow our email Element to override our settings
        if ($emailTemplateId && class_exists($emailTemplateId)) {
            $emailTemplates = new $emailTemplateId();

            if (!$emailTemplates instanceof EmailTemplates) {
                // if a class but does not extend EmailTemplates
                $isCustom = true;
            }
        } else {
            // if emailTemplateId is a string
            $isCustom = true;
        }

        if ($isCustom) {
            // custom folder on site path
            $templatePath = $sitePath.DIRECTORY_SEPARATOR.$emailTemplateId;

            $emailTemplates = new CustomTemplates();
            $emailTemplates->setPath($templatePath);
        }

        // Set the EmailElement on the Email Template Object
        $emailTemplates->email = $this;

        // Cache the EmailTemplates for subsequent calls
        $this->setEmailTemplates($emailTemplates);

        return $emailTemplates;
    }

    /**
     * The Email Service provide can be update via Craft's Email Settings
     *
     * @return Mailer
     */
    public function getMailer(): Mailer
    {
        return new DefaultMailer();
    }

    /**
     * @return array
     */
    public function getFieldLayoutTabs(): array
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
                            /**
                             * @var $field Field
                             */
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

    /**
     * Confirm that an email is enabled.
     *
     * @return bool
     */
    public function isReady(): bool
    {
        return ($this->getStatus() == static::ENABLED);
    }
}