<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\email\base;

use barrelstrength\sproutbase\app\email\models\SimpleRecipient;
use barrelstrength\sproutbase\app\email\models\SimpleRecipientList;
use barrelstrength\sproutemail\elements\CampaignEmail;
use barrelstrength\sproutemail\models\CampaignType;
use barrelstrength\sproutlists\records\Lists as ListsRecord;
use barrelstrength\sproutlists\elements\Subscribers;
use craft\base\Element;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use Craft;
use craft\mail\Message;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use yii\base\Model;

/**
 * @mixin NotificationEmailSenderInterface
 */
abstract class Mailer
{
    /**
     * The settings for this mailer
     *
     * @var Model
     */
    protected $settings;

    /**
     * @var SimpleRecipient[]
     */
    private $_onTheFlyRecipients = [];

    /**
     * @var EmailElement
     */
    private $emailElement;

    /**
     * Returns a list of On The Fly Recipients
     *
     * @return SimpleRecipient[]
     */
    public function getOnTheFlyRecipients()
    {
        return $this->_onTheFlyRecipients;
    }

    /**
     * Sets a list of On The Fly Recipients
     *
     * @param array $onTheFlyRecipients Array of Email Addresses
     */
    public function setOnTheFlyRecipients($onTheFlyRecipients = [])
    {
        $recipients = [];

        if ($onTheFlyRecipients) {
            foreach ($onTheFlyRecipients as $onTheFlyRecipient) {
                $recipient = new SimpleRecipient();
                $recipient->email = $onTheFlyRecipient;

                $recipients[] = $recipient;
            }
        }

        $this->_onTheFlyRecipients = $recipients;
    }


    /**
     * Returns the Mailer Title when used in string context
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * The Mailer Name
     *
     * @example Sprout Email
     * @example AWS
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Returns a short description of this mailer
     *
     * @example The Sprout Email Mailer uses the Craft API to send emails
     *
     * @return string
     */
    abstract public function getDescription();

    /**
     * Returns whether or not the mailer has registered routes to accomplish tasks within Sprout Email
     *
     * @return bool
     */
    public function hasCpSection()
    {
        return false;
    }

    /**
     * Returns whether or not the mailer has settings to display
     *
     * @return bool
     */
    public function hasCpSettings()
    {
        $settings = $this->defineSettings();

        return is_array($settings) && $settings;
    }

    /**
     * Returns the URL for this Mailer's CP Settings
     *
     * @return null|string
     */
    public function getCpSettingsUrl()
    {
        if (!$this->hasCpSettings()) {
            return null;
        }

        // @todo - getId no longer exists, review
        return UrlHelper::cpUrl('sprout-email/settings/mailers/'.$this->getId());
    }

    /**
     * @todo - do we need to define settings any longer? Or can we just use variables on the specific Mailer Class?
     *
     * Enables mailers to define their own settings and validation for them
     *
     * @return array
     */
    public function defineSettings()
    {
        return [];
    }

    /**
     * Returns the value that should be saved to the settings column for this mailer
     *
     * @example
     * return craft()->request->getPost('sproutemail');
     *
     * @return mixed
     */
    public function prepSettings()
    {
        // @todo - getId no longer exists, review
        return Craft::$app->getRequest()->getParam($this->getId());
    }

    /**
     * Returns the settings model for this mailer
     *
     * @return Model
     */
    public function getSettings()
    {
        $currentPluginHandle = Craft::$app->request->getSegment(1);

        $plugin = Craft::$app->plugins->getPlugin($currentPluginHandle);

        $settings = null;

        if ($plugin) {
            $settings = $plugin->getSettings();
        }

        return $settings;
    }

    /**
     * Returns a rendered html string to use for capturing settings input
     *
     * @param array $settings
     *
     * @return string|Model
     */
    public function getSettingsHtml(array $settings = [])
    {
        return '';
    }

    /**
     * Allow modification of campaignType model before it is saved.
     *
     * @param CampaignType $model
     *
     * @return CampaignType
     */
    public function prepareSave(CampaignType $model)
    {
        return $model;
    }

    /**
     * Gives mailers the ability to include their own modal resources and register their dynamic action handlers
     *
     * @example
     * Mailers should be calling the following functions from within their implementation
     *
     * craft()->templates->includeJs(File|Resource)();
     * craft()->templates->includeCss(File|Resource)();
     *
     * @note
     * To register a dynamic action handler, mailers should listen for sproutEmailBeforeRender
     * $(document).on('sproutEmailBeforeRender', function(e, content) {});
     */
    public function includeModalResources()
    {
    }

    /**
     * Gives a mailer the ability to register an action to post to when a [prepare] modal is launched
     *
     * @return string
     */
    public function getActionForPrepareModal()
    {
        return 'sprout-email/mailer/get-prepare-modal';
    }

    /**
     * @param CampaignEmail $campaignEmail
     * @param CampaignType  $campaignType
     *
     * @return mixed
     */
    abstract public function getPrepareModalHtml(CampaignEmail $campaignEmail, CampaignType $campaignType);

    /**
     * Return true to allow and show mailer dynamic recipients
     *
     * @return bool
     */
    public function hasInlineRecipients()
    {
        return false;
    }

    /**
     * Returns whether this Mailer supports mailing lists
     *
     * @return bool Whether this Mailer supports lists. Default is `true`.
     */
    public function hasLists()
    {
        return true;
    }

    /**
     * Returns the Lists available to this Mailer
     */
    public function getLists()
    {
        return [];
    }

    /**
     * Returns the HTML for our List Settings on the Campaign and Notification Email edit page
     *
     * @param array $values
     *
     * @return null
     */
    public function getListsHtml($values = [])
    {
        return null;
    }

    /**
     * Prepare the list data before we save it in the database
     *
     * @param $lists
     *
     * @return mixed
     */
    public function prepListSettings($lists)
    {
        return $lists;
    }

    /**
     * @param Element $email
     *
     * @return Element
     */
    public function beforeValidate(Element $email)
    {
        return $email;
    }

    public function sendCampaignEmail(CampaignEmail $campaignEmail, CampaignType $campaignType)
    {
        return null;
    }

    /**
     * @param $campaignEmail
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getRecipientsHtml($campaignEmail)
    {
        $defaultFromName = "";
        $defaultFromEmail = "";
        $defaultReplyTo = "";

        return Craft::$app->getView()->renderTemplate('sprout-base-email/_components/mailers/recipients-html', [
            'campaignEmail' => $campaignEmail,
            'defaultFromName' => $defaultFromName,
            'defaultFromEmail' => $defaultFromEmail,
            'defaultReplyTo' => $defaultReplyTo,
        ]);
    }

    /**
     * @param EmailElement $email
     *
     * @return SimpleRecipientList
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function getRecipientList(EmailElement $email)
    {
        $recipientList = new SimpleRecipientList();

        $validator = new EmailValidator();
        $multipleValidations = new MultipleValidationWithAnd([
            new RFCValidation()
        ]);

        // Add any On The Fly Recipients to our List
        if ($onTheFlyRecipients = $this->getOnTheFlyRecipients()) {
            foreach ($onTheFlyRecipients as $onTheFlyRecipient) {
                if ($validator->isValid($onTheFlyRecipient->email, $multipleValidations)) {
                    $recipientList->addRecipient($onTheFlyRecipient);
                }

                $recipientList->addInvalidRecipient($onTheFlyRecipient);
            }

            // On the Fly Recipients are added in Test Modals and override all other
            // potential recipients.
            return $recipientList;
        }

        $recipientList = $this->getRecipients($email->recipients, $email);

        // @todo - test this integration
        if (Craft::$app->getPlugins()->getPlugin('sprout-lists')) {

            $listRecipients = $this->getRecipientsFromSelectedLists($email->listSettings);

            if ($listRecipients) {
                foreach ($listRecipients as $listRecipient) {

                    if ($validator->isValid($listRecipient->email, $multipleValidations)) {
                        $recipientList->addRecipient($listRecipient);
                    } else {
                        $recipientList->addInvalidRecipient($listRecipient);
                    }
                }
            }
        }

        return $recipientList;
    }

    public function getRecipientsFromSelectedLists($listSettings)
    {
        $listIds = [];
        // Convert json format to array
        if ($listSettings != null AND is_string($listSettings)) {
            $listIds = Json::decode($listSettings);
            $listIds = $listIds['listIds'];
        }

        if (empty($listIds)) {
            return [];
        }

        // Get all subscribers by list IDs from the Subscriber ListType
        $listRecords = ListsRecord::find()
            ->where([
                'id' => $listIds
            ])
            ->all();


        $sproutListsRecipientsInfo = [];
        if ($listRecords != null) {
            foreach ($listRecords as $listRecord) {
                if (!empty($listRecord->subscribers)) {

                    /** @var Subscribers $subscriber */
                    foreach ($listRecord->subscribers as $subscriber) {
                        // Assign email as key to not repeat subscriber
                        $sproutListsRecipientsInfo[$subscriber->email] = $subscriber->getAttributes();
                    }
                }
            }
        }

        // @todo - review what attributes are passed for recipients.
        $listRecipients = [];
        if ($sproutListsRecipientsInfo) {
            foreach ($sproutListsRecipientsInfo as $listRecipient) {
                $recipientModel = new SimpleRecipient();

                $firstName = $listRecipient['firstName'] ?? '';
                $lastName = $listRecipient['lastName'] ?? '';
                $name = $firstName.' '.$lastName;

                $recipientModel->name = trim($name) ?? null;
                $recipientModel->email = $listRecipient['email'] ?? null;

                $listRecipients[] = $recipientModel;
            }
        }

        return $listRecipients;
    }

    /**
     * Get SimpleRecipient objects group in valid and invalid emails
     *
     * @param $recipients
     * @param $email
     *
     * @return SimpleRecipientList
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function getRecipients($recipients, $email)
    {
        $recipientList = new SimpleRecipientList;

        $validator = new EmailValidator();
        $multipleValidations = new MultipleValidationWithAnd([
            new RFCValidation()
        ]);

        if (!empty($recipients)) {
            // Recipients are added as a comma-delimited list. While not on a formal list,
            // they are considered permanent and will be included alongside any more formal lists
            // Recipients can be dynamic values if matched to a value in the Event Object
            $recipients = Craft::$app->getView()->renderObjectTemplate($recipients, $email->getEventObject());

            $recipientArray = explode(',', $recipients);

            foreach ($recipientArray as $recipient) {
                $recipientModel = new SimpleRecipient();
                $recipientModel->email = trim($recipient);

                if ($validator->isValid($recipientModel->email, $multipleValidations)) {
                    $recipientList->addRecipient($recipientModel);
                } else {
                    $recipientList->addInvalidRecipient($recipientModel);
                }
            }
        }

        return $recipientList;
    }

    /**
     * Prepares the NotificationEmail Element and returns a Message model.
     *
     * @param EmailElement $email
     *
     * @return Message
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function getMessage(EmailElement $email)
    {
        $object = $email->getEventObject();

        $message = new Message();

        // Render Email Entry fields that have dynamic values
        $this->renderObjectTemplateSafely($email, 'subjectLine', $object);
        $this->renderObjectTemplateSafely($email, 'fromName', $object);
        $this->renderObjectTemplateSafely($email, 'fromEmail', $object);
        $this->renderObjectTemplateSafely($email, 'replyToEmail', $object);

        $message->setSubject($email->subjectLine);
        $message->setFrom([$email->fromEmail => $email->fromName]);
        $message->setReplyTo($email->replyToEmail);

        // Our templates take a few steps to process
        $textBody = '';
        $htmlBody = '';

        // Get the initial rendering of the templates
        try {
            $textBody = $email->getEmailTemplates()->getTextBody();
            $htmlBody = $email->getEmailTemplates()->getHtmlBody();
        } catch (\Exception $e) {
            $email->addError('template', $e->getMessage());
        }

        if (empty($textBody)) {
            $email->addError('body', Craft::t('sprout-base', 'Text template is blank.'));
        }

        if (empty($htmlBody)) {
            $email->addError('htmlBody', Craft::t('sprout-base', 'HTML template is blank.'));
        }

        $styleTags = [];

        // Swap out Style tags so we don't run into conflicts with shorthand object-syntax
        $htmlBody = $this->addPlaceholderStyleTags($htmlBody, $styleTags);

        // Some Twig code in our email fields may need us to decode
        // entities so our email doesn't throw errors when we try to
        // render the field objects. Example: {variable|date("Y/m/d")}
        $textBody = Html::decode($textBody);
        $htmlBody = Html::decode($htmlBody);

        // Process the results of the templates once more, to render any dynamic objects used in fields
        try {
            $textBody = Craft::$app->getView()->renderObjectTemplate($textBody, $object);
        } catch (\Exception $e) {
            $email->addError('body', $e->getMessage());
        }

        try {
            $htmlBody = Craft::$app->getView()->renderObjectTemplate($htmlBody, $object);
        } catch (\Exception $e) {
            $email->addError('htmlBody', $e->getMessage());
        }

        $htmlBody = $this->removePlaceholderStyleTags($htmlBody, $styleTags);

        $message->setTextBody($textBody);
        $message->setHtmlBody($htmlBody);

        // Make sure we use the HTML and Text after they are processed the second time
        $email->getEmailTemplates()->setTextBody($textBody);
        $email->getEmailTemplates()->setHtmlBody($htmlBody);

        return $message;
    }

    /**
     * Render a specific attribute on the EmailElement model and add an error to
     * the model if something goes wrong.
     *
     * @param EmailElement $email
     * @param              $attribute
     * @param              $object
     *
     * @throws \Throwable
     */
    public function renderObjectTemplateSafely(EmailElement $email, $attribute, $object)
    {
        try {
            $email->{$attribute} = Craft::$app->getView()->renderObjectTemplate($email->{$attribute}, $object);
        } catch (\Exception $e) {
            $email->addError($email->{$attribute}, $e->getMessage());
        }
    }

    public function addPlaceholderStyleTags($htmlBody, &$styleTags)
    {
        // Get the style tag
        preg_match_all("/<style\\b[^>]*>(.*?)<\\/style>/s", $htmlBody, $matches);

        if (!empty($matches)) {
            $tags = $matches[0];

            // Temporarily replace with style tags with a random string
            if (!empty($tags)) {
                $i = 0;
                foreach ($tags as $tag) {
                    $key = "<!-- %style$i% -->";

                    $styleTags[$key] = $tag;

                    $htmlBody = str_replace($tag, $key, $htmlBody);

                    $i++;
                }
            }
        }

        return $htmlBody;
    }

    public function removePlaceholderStyleTags($htmlBody, $styleTags)
    {
        if (!empty($styleTags)) {
            foreach ($styleTags as $key => $tag) {
                $htmlBody = str_replace($key, $tag, $htmlBody);
            }
        }

        return $htmlBody;
    }
}