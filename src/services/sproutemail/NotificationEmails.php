<?php

namespace barrelstrength\sproutbase\services\sproutemail;

use barrelstrength\sproutbase\base\TemplateTrait;
use barrelstrength\sproutbase\elements\sproutemail\NotificationEmail;
use barrelstrength\sproutbase\mailers\DefaultMailer;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\models\sproutemail\Message;
use barrelstrength\sproutbase\models\sproutbase\Response;
use barrelstrength\sproutbase\records\sproutemail\NotificationEmail as NotificationEmailRecord;
use craft\base\Component;
use Craft;
use craft\helpers\ElementHelper;
use craft\helpers\Html;
use craft\helpers\UrlHelper;
use League\HTMLToMarkdown\HtmlConverter;
use craft\base\ElementInterface;
use yii\web\NotFoundHttpException;

/**
 * Class NotificationEmails
 *
 * @package barrelstrength\sproutbase\services\sproutemail
 */
class NotificationEmails extends Component
{
    use TemplateTrait;

    /**
     * @param NotificationEmail $notificationEmail
     *
     * @return bool
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function saveNotification(NotificationEmail $notificationEmail)
    {
        $isNewNotificationEmail = !$notificationEmail->id;

        if (!$notificationEmail->validate()) {
            SproutBase::info(Craft::t('sprout-base', 'Notification Email not saved due to validation error.'));
            return false;
        }

        if (!$isNewNotificationEmail) {
            $notificationEmailRecord = NotificationEmail::findOne($notificationEmail->id);

            if (!$notificationEmailRecord) {
                throw new NotFoundHttpException(Craft::t('sprout-base', 'No entry exists with the ID “{id}”', ['id' => $notificationEmail->id]));
            }
        } else {
            $notificationEmailRecord = new NotificationEmailRecord();
        }

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            // Save the Field Layout
            $fieldLayout = $notificationEmail->getFieldLayout();
            Craft::$app->getFields()->saveLayout($fieldLayout);
            $notificationEmail->fieldLayoutId = $fieldLayout->id;
            $notificationEmailRecord->fieldLayoutId = $fieldLayout->id;

            // Save the global set
            if (!Craft::$app->getElements()->saveElement($notificationEmail, false)) {
                return false;
            }

            // Now that we have an element ID, save the record
            if ($isNewNotificationEmail) {
                $notificationEmailRecord->id = $notificationEmail->id;
            }

            $transaction->commit();

            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
    }

    /**
     * Deletes a Notification Email by ID
     *
     * @param $id
     *
     * @return bool
     * @throws \Throwable
     */
    public function deleteNotificationEmailById($id)
    {
        return Craft::$app->getElements()->deleteElementById($id);
    }

    /**
     * Returns all campaign notifications based on the passed in event id
     *
     * @param string $eventId
     *
     * @return ElementInterface[]|null
     */
    public function getAllNotificationEmails($eventId = null)
    {
        $notifications = NotificationEmail::find();

        if ($eventId) {
            $attributes = ['eventId' => $eventId];
            $notifications = $notifications->where($attributes);
        }

        return $notifications->all();
    }

    /**
     * @param $emailId
     *
     * @return NotificationEmail|ElementInterface
     */
    public function getNotificationEmailById($emailId)
    {
        return Craft::$app->getElements()->getElementById($emailId);
    }

    /**
     * Prepares the NotificationEmail Element and returns a Message model.
     *
     * @param         $notificationEmail
     * @param null    $object
     *
     * @return Message
     * @throws \yii\base\Exception
     */
    public function getNotificationEmailMessage(NotificationEmail $notificationEmail, $object = null)
    {
        // Render Email Entry fields that have dynamic values
        $subject = $this->renderObjectTemplateSafely($notificationEmail->subjectLine, $object);
        $fromName = $this->renderObjectTemplateSafely($notificationEmail->fromName, $object);
        $fromEmail = $this->renderObjectTemplateSafely($notificationEmail->fromEmail, $object);
        $replyTo = $this->renderObjectTemplateSafely($notificationEmail->replyToEmail, $object);

        $view = Craft::$app->getView();
        $oldTemplatePath = $view->getTemplatesPath();

        $emailTemplatePath = SproutBase::$app->sproutEmail->getEmailTemplate($notificationEmail);

        $this->setFolderPath($emailTemplatePath);

        $htmlEmailTemplate = 'email.html';
        $textEmailTemplate = 'email.txt';

        $view->setTemplatesPath($emailTemplatePath);

        $htmlBody = $this->renderSiteTemplateIfExists($htmlEmailTemplate, [
            'email' => $notificationEmail,
            'object' => $object
        ]);

        $textEmailTemplateExists = Craft::$app->getView()->doesTemplateExist($textEmailTemplate);

        // Converts html body to text email if no .txt
        if ($textEmailTemplateExists) {
            $body = $this->renderSiteTemplateIfExists($textEmailTemplate, [
                'email' => $notificationEmail,
                'object' => $object
            ]);
        } else {
            $converter = new HtmlConverter([
                'strip_tags' => true
            ]);

            // For more advanced html templates, conversion may be tougher. Minifying the HTML
            // can help and ensuring that content is wrapped in proper tags that adds spaces between
            // things in Markdown, like <p> tags or <h1> tags and not just <td> or <div>, etc.
            $markdown = $converter->convert($htmlBody);

            $body = trim($markdown);
        }

        $view->setTemplatesPath($oldTemplatePath);

        $message = new Message();

        $message->setSubject($subject);
        $message->setFrom([$fromEmail => $fromName]);
        $message->setReplyTo($replyTo);
        $message->setTextBody($body);
        $message->setHtmlBody($htmlBody);

        // Store our rendered email for later. We save this as separate variables as the Message Class
        // we extend doesn't have a way to access these items once we set them.
        $message->renderedBody = $body;
        $message->renderedHtmlBody = $htmlBody;

        $styleTags = [];

        $htmlBody = $this->addPlaceholderStyleTags($htmlBody, $styleTags);

        // Some Twig code in our email fields may need us to decode
        // entities so our email doesn't throw errors when we try to
        // render the field objects. Example: {variable|date("Y/m/d")}

        $body = Html::decode($body);
        $htmlBody = Html::decode($htmlBody);

        // Process the results of the template s once more, to render any dynamic objects used in fields
        $body = $this->renderObjectTemplateSafely($body, $object);
        $message->setTextBody($body);

        $htmlBody = $this->renderObjectTemplateSafely($htmlBody, $object);

        $htmlBody = $this->removePlaceholderStyleTags($htmlBody, $styleTags);
        $message->setHtmlBody($htmlBody);

        return $message;
    }

    /**
     * @param ElementInterface $notificationEmail
     * @param                  $object - will be an element model most of the time
     *
     * @return bool|null
     * @throws \Exception
     */
    public function sendNotificationViaMailer(ElementInterface $notificationEmail, $object)
    {
        $mailer = SproutBase::$app->mailers->getMailerByName(DefaultMailer::class);

        if (!method_exists($mailer, 'sendNotificationEmail')) {
            throw new \BadMethodCallException(Craft::t('sprout-base', 'The {mailer} does not have a sendNotificationEmail() method.',
                ['mailer' => get_class($mailer)]));
        }

        try {

            if ($mailer) {
                /**
                 * @var $notificationEmail NotificationEmail
                 */
                return $mailer->sendNotificationEmail($notificationEmail, $object);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return null;
    }

    /**
     * @param NotificationEmail $notificationEmail
     *
     * @return bool
     */
    public function sendTestNotificationEmail(NotificationEmail $notificationEmail)
    {
        $event = SproutBase::$app->notificationEvents->getEventById($notificationEmail->eventId);
        $mailer = SproutBase::$app->mailers->getMailerByName(DefaultMailer::class);

        if (!$event or !$mailer) {
            return false;
        }

        $options = json_decode($notificationEmail->options, true);

        // Must pass email options for getMockedParams methods to use $this->options
        $event->setOptions($options);

        try {

            if (!$mailer->sendNotificationEmail($notificationEmail, $event->getMockedParams()))
            {
                $customErrorMessage = SproutBase::$app->common->getErrors();

                if (!empty($customErrorMessage)) {
                    $message = $customErrorMessage;
                } else {
                    $message = Craft::t('sprout-base', 'Unable to send Test Notification Email.');
                }

                SproutBase::error($message);

                return false;
            }

            return true;

        } catch (\Exception $e) {
            SproutBase::$app->common->addError('notification-mock-error', $e->getMessage());

            return false;
        }
    }

    /**
     * @todo - doesn't appear to be in use. Confirm.
     *
     * Returns an array or variables for a notification template
     *
     * @example
     * The following syntax is supported to access event element properties
     *
     * {attribute}
     * {{ attribute }}
     * {{ object.attribute }}
     *
     * @param NotificationEmail $notificationEmail
     * @param mixed|null        $element
     *
     * @return array
     */
//    public function prepareNotificationTemplateVariables(NotificationEmail $notificationEmail, $element = null)
//    {
//        if (is_object($element) && method_exists($element, 'getAttributes')) {
//            $attributes = $element->getAttributes();
//
//            if (isset($element->elementType)) {
//                $content = $element->getContent()->getAttributes();
//
//                if (count($content)) {
//                    foreach ($content as $key => $value) {
//                        if (!isset($attributes[$key])) {
//                            $attributes[$key] = $value;
//                        }
//                    }
//                }
//            }
//        } else {
//            $attributes = (array)$element;
//        }
//
//        return array_merge($attributes, [
//            'email' => $notificationEmail,
//            'object' => $element
//        ]);
//    }

    /**
     * @param $notificationId
     *
     * @return Response
     */
    public function getPrepareModal($notificationId)
    {
        $notificationEmail = Craft::$app->getElements()->getElementById($notificationId);

        $response = new Response();
        /**
         * @var $notificationEmail NotificationEmail
         */
        if ($notificationEmail) {
            try {
                $response->success = true;
                $response->content = $this->getPrepareModalHtml($notificationEmail);

                return $response;
            } catch (\Exception $e) {
                $response->success = false;
                $response->message = $e->getMessage();

                return $response;
            }
        } else {
            $response->success = false;

            $response->message = Craft::t('sprout-base', 'No actions available for this notification.');
        }

        return $response;
    }

    /**
     * @param NotificationEmail $notificationEmail
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getPrepareModalHtml(NotificationEmail $notificationEmail)
    {
        // Display the testToEmailAddress if it exists
        $recipients = Craft::$app->getConfig()->getGeneral()->testToEmailAddress;

        if (empty($recipients)) {
            $currentUser = Craft::$app->getUser()->getIdentity();
            $recipients = $currentUser->email;
        }

        $errors = [];

        $errors = $this->getNotificationErrors($notificationEmail, $errors);

        return Craft::$app->getView()->renderTemplate(
            'sprout-base/sproutemail/_modals/prepare-email-snapshot',
            [
                'email' => $notificationEmail,
                'recipients' => $recipients,
                'errors' => $errors
            ]
        );
    }

    /**
     * Retrieves a rendered Notification Email to be shared or for Live Preview
     *
     * @param      $notificationId
     * @param null $type
     *
     * @throws \yii\base\Exception
     * @throws \yii\base\ExitException
     */
    public function getPreviewNotificationEmailById($notificationId, $type = null)
    {
        /**
         * @var $notificationEmail NotificationEmail
         */
        $notificationEmail = $this->getNotificationEmailById($notificationId);

        $eventId = $notificationEmail->eventId;

        $event = SproutBase::$app->notificationEvents->getEventById($eventId);

        if (!$event) {
            ob_start();

            echo Craft::t('sprout-base', 'Notification Email cannot display. The Event setting must be set.');

            // End the request

            Craft::$app->end();
        }

        $event->setOptions($notificationEmail->options);

        $template = $notificationEmail->emailTemplateId;

        if (empty($notificationEmail->emailTemplateId)) {
            /**
             * Get the templates path for the sprout base default notification template
             */
            $template = SproutBase::$app->sproutEmail->getEmailTemplate($notificationEmail);
        }

        // The getBodyParam is for livePreviewNotification to update on change
        $subjectLine = Craft::$app->getRequest()->getBodyParam('subjectLine');
        if ($subjectLine) {
            $notificationEmail->subjectLine = $subjectLine;
        }

        $defaultBody = Craft::$app->getRequest()->getBodyParam('defaultBody');

        if ($defaultBody) {
            $notificationEmail->defaultBody = $defaultBody;
        }

        $fieldsLocation = Craft::$app->getRequest()->getParam('fieldsLocation', 'fields');
        $notificationEmail->setFieldValuesFromRequest($fieldsLocation);

        $fileExtension = ($type != null && $type == 'text') ? 'txt' : 'html';

        $message = $this->getNotificationEmailMessage($notificationEmail);

        $this->showPreviewEmail($message, $fileExtension);
    }

    /**
     * @param        $message
     * @param string $fileExtension
     *
     * @throws \yii\base\ExitException
     */
    public function showPreviewEmail($message, $fileExtension = 'html')
    {
        if ($fileExtension == 'txt') {
            $output = $message->renderedBody;
        } else {
            $output = $message->renderedHtmlBody;
        }

        // Output it into a buffer, in case TasksService wants to close the connection prematurely
        ob_start();

        echo $output;

        // End the request
        Craft::$app->end();
    }

    /**
     * @param       $notificationEmail
     * @param array $errors
     *
     * @return array
     * @throws \yii\base\Exception
     */
    public function getNotificationErrors($notificationEmail, array $errors = [])
    {
        $currentPluginHandle = Craft::$app->request->getSegment(1);

        $notificationEditUrl = UrlHelper::cpUrl($currentPluginHandle.'/notifications/edit/'.$notificationEmail->id);
        $notificationEditSettingsUrl = UrlHelper::cpUrl($currentPluginHandle.'/settings/notifications/edit/'.
            $notificationEmail->id);

        $event = SproutBase::$app->notificationEvents->getEventById($notificationEmail->eventId);

        $template = SproutBase::$app->sproutEmail->getEmailTemplate($notificationEmail);

        if ($event === null) {
            $errors[] = Craft::t('sprout-base', 'No Event is selected. <a href="{url}">Edit Notification</a>.', [
                'url' => $notificationEditUrl
            ]);
        }

        if (empty($template)) {
            $errors[] = Craft::t('sprout-base', 'No template added. <a href="{url}">Edit Notification Settings</a>.',
                [
                    'url' => $notificationEditSettingsUrl
                ]);
        }

        if (count($errors)) {
            return $errors;
        }

        $options = json_decode($notificationEmail->options, true);

        $event->setOptions($options);

        $mockObject = $event->getMockedParams();

        $this->getNotificationEmailMessage($notificationEmail, $mockObject);

        $templateErrors = SproutBase::$app->common->getErrors();

        if (!empty($templateErrors['template'])) {

            foreach ($templateErrors['template'] as $templateError) {
                $errors[] = Craft::t('sprout-base', $templateError.' <a href="{url}">Edit Settings</a>.',
                    [
                        'url' => $notificationEditSettingsUrl
                    ]);
            }
        }

        return $errors;
    }

    /**
     * @param string $subjectLine
     * @param string $handle
     *
     * @return NotificationEmail|null
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function createNewNotification($subjectLine = null, $handle = null)
    {
        $currentPluginHandle = Craft::$app->request->getSegment(1);

        $notificationEmail = new NotificationEmail();
        $subjectLine = $subjectLine ?? Craft::t('sprout-base', 'Notification');
        $handle = $handle ?? ElementHelper::createSlug($subjectLine);

        $subjectLine = $this->getFieldAsNew('subjectLine', $subjectLine);

        $notificationEmail->title = $subjectLine;
        $notificationEmail->subjectLine = $subjectLine;
        $notificationEmail->pluginId = $currentPluginHandle;
        $notificationEmail->slug = $handle;

        $systemEmailSettings = Craft::$app->getSystemSettings()->getEmailSettings();

        // @todo - add override settings to Sprout Email
        $notificationEmail->fromName = $systemEmailSettings->fromName;
        $notificationEmail->fromEmail = $systemEmailSettings->fromEmail;

        // Set default tab
        $field = null;

        if ($this->saveNotification($notificationEmail)) {

            return $notificationEmail;
        }

        return null;
    }

    public function getFieldAsNew($field, $value)
    {
        $newField = null;
        $i = 1;
        $band = true;
        do {
            $newField = $field == 'handle' ? $value.$i : $value.' '.$i;
            $form = $this->getFieldValue($field, $newField);
            if ($form === null) {
                $band = false;
            }

            $i++;
        } while ($band);

        return $newField;
    }

    public function getFieldValue($field, $value)
    {
        return NotificationEmailRecord::findOne([
            $field => $value
        ]);
    }
}
