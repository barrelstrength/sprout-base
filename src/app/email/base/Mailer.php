<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\email\base;

use barrelstrength\sproutbase\app\email\models\SimpleRecipient;
use barrelstrength\sproutbase\app\email\models\SimpleRecipientList;
use craft\base\Component;
use craft\helpers\Html;
use Craft;
use craft\mail\Message;
use yii\base\Model;

/**
 * Class Mailer
 *
 *
 * @property SimpleRecipient[]|array $onTheFlyRecipients
 * @property array                   $lists
 * @property string                  $name
 * @property string                  $description
 * @property string                  $actionForPrepareModal
 * @property string                  $prepareModalHtml
 * @property SimpleRecipientList     $recipientList
 * @property string                  $recipientsHtml
 * @property null|string             $cpSettingsUrl
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
     * @example The Sprout Email Mailer uses the Craft API to send emails
     *
     * @return string
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
     * @throws \Throwable
     * @throws \yii\base\Exception
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
    private function renderObjectTemplateSafely(EmailElement $email, $attribute, $object)
    {
        try {
            $email->{$attribute} = Craft::$app->getView()->renderObjectTemplate($email->{$attribute}, $object);
        } catch (\Exception $e) {
            $email->addError($email->{$attribute}, $e->getMessage());
        }
    }

    private function addPlaceholderStyleTags($htmlBody, &$styleTags)
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

    private function removePlaceholderStyleTags($htmlBody, $styleTags)
    {
        if (!empty($styleTags)) {
            foreach ($styleTags as $key => $tag) {
                $htmlBody = str_replace($key, $tag, $htmlBody);
            }
        }

        return $htmlBody;
    }
}