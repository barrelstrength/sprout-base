<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\email\base;

use Craft;
use craft\base\Component;
use craft\mail\Message;
use Exception;
use Throwable;
use yii\base\Model;

/**
 *
 * @property string      $description
 * @property null|string $cpSettingsUrl
 */
abstract class Mailer extends Component
{
    use RecipientsTrait;
    use ModalWorkflowTrait;

    /**
     * The settings for this mailer
     *
     * @var Model
     */
    protected $settings;

    /**
     * Returns the Mailer Title when used in string context
     *
     * @return string
     */
    public function __toString()
    {
        return self::displayName();
    }

    /**
     * Returns a short description of this mailer
     *
     * @return string
     * @example The Sprout Email Mailer uses the Craft API to send emails
     *
     */
    abstract public function getDescription(): string;

    /**
     * Returns whether or not the mailer has registered routes to accomplish tasks within Sprout Email
     *
     * @return bool
     */
    public function hasCpSection(): bool
    {
        return false;
    }

    /**
     * Returns the URL for this Mailer's CP Settings
     *
     * @return null|string
     */
    public function getCpSettingsUrl()
    {
        return null;
    }

    /**
     * Returns the settings model for this mailer
     *
     * @return Model
     */
    public function getSettings(): Model
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
     * Prepares the NotificationEmail Element and returns a Message model.
     *
     * @param EmailElement $email
     *
     * @return Message
     * @throws Throwable
     */
    public function getMessage(EmailElement $email): Message
    {
        $object = $email->getEventObject();

        $message = new Message();

        // Render Email Entry fields that have dynamic values
        $this->renderObjectTemplateSafely($email, 'subjectLine', $object);
        $this->renderObjectTemplateSafely($email, 'fromName', $object);
        $this->renderObjectTemplateSafely($email, 'fromEmail', $object);
        $this->renderObjectTemplateSafely($email, 'replyToEmail', $object);
        $this->renderObjectTemplateSafely($email, 'defaultBody', $object);


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
        } catch (Exception $e) {
            $email->addError('template', $e->getMessage());
        }

        if (empty($textBody)) {
            $email->addError('body', Craft::t('sprout', 'Text template is blank.'));
        }

        if (empty($htmlBody)) {
            $email->addError('htmlBody', Craft::t('sprout', 'HTML template is blank.'));
        }

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
     * @throws Throwable
     */
    private function renderObjectTemplateSafely(EmailElement $email, $attribute, $object)
    {
        try {
            // Make sure we don't process any null values
            $attributeString = $email->{$attribute} ?? '';
            $email->{$attribute} = Craft::$app->getView()->renderObjectTemplate($attributeString, $object);
        } catch (Exception $e) {
            $email->addError($attribute, $e->getMessage());
        }
    }
}