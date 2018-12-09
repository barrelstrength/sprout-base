<?php

namespace barrelstrength\sproutbase\app\email\services;

use barrelstrength\sproutbase\app\email\base\EmailElement;
use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\base\NotificationEmailSenderInterface;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\app\email\models\ModalResponse;
use barrelstrength\sproutbase\app\email\records\NotificationEmail as NotificationEmailRecord;
use craft\base\Component;
use Craft;
use craft\helpers\ElementHelper;

use craft\helpers\UrlHelper;
use craft\base\ElementInterface;

/**
 * Class NotificationEmails
 *
 * @package barrelstrength\sproutbase\app\email\services
 */
class NotificationEmails extends Component
{
    /**
     * @param NotificationEmail $notificationEmail
     *
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function saveNotification(NotificationEmail $notificationEmail)
    {
        if (!$notificationEmail->validate()) {
            SproutBase::info(Craft::t('sprout-base', 'Notification Email not saved due to validation error.'));
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            // Save the Field Layout
            $fieldLayout = $notificationEmail->getFieldLayout();
            Craft::$app->getFields()->saveLayout($fieldLayout);
            $notificationEmail->fieldLayoutId = $fieldLayout->id;

            // Save the global set
            if (!Craft::$app->getElements()->saveElement($notificationEmail, false)) {
                return false;
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
    public function deleteNotificationEmailById($id): bool
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
     * @param int $emailId
     *
     * @return NotificationEmail|null
     */
    public function getNotificationEmailById(int $emailId)
    {
        /** @var NotificationEmail|null $notificationEmail */
        $notificationEmail = Craft::$app->getElements()->getElementById($emailId);

        return $notificationEmail;
    }

    /**
     * @param NotificationEmail $notificationEmail
     *
     * @return bool
     * @throws \Throwable
     */
    public function sendNotificationViaMailer(NotificationEmail $notificationEmail)
    {
        try {
            /** @var Mailer|NotificationEmailSenderInterface $mailer */
            $mailer = $notificationEmail->getMailer();

            return $mailer->sendNotificationEmail($notificationEmail);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Retrieves a rendered Notification Email to be shared or for Live Preview
     *
     * @param      $notificationId
     * @param null $type
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\base\ExitException
     */
    public function getPreviewNotificationEmailById($notificationId, $type = null)
    {
        /**
         * @var $notificationEmail NotificationEmail
         */
        $notificationEmail = $this->getNotificationEmailById($notificationId);

        $event = SproutBase::$app->notificationEvents->getEvent($notificationEmail);

        if (!$event) {
            ob_start();

            echo Craft::t('sprout-base', 'Notification Email cannot display. The Event setting must be set.');

            // End the request

            Craft::$app->end();
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

        $notificationEmail->setEventObject($event->getMockEventObject());

        $this->showPreviewEmail($notificationEmail, $fileExtension);
    }

    /**
     * @param NotificationEmail $email
     * @param string            $fileExtension
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\base\ExitException
     */
    public function showPreviewEmail(NotificationEmail $email, $fileExtension = 'html')
    {
        if ($fileExtension == 'txt') {
            $output = $email->getEmailTemplates()->getTextBody();
        } else {
            $output = $email->getEmailTemplates()->getHtmlBody();
        }

        $event = SproutBase::$app->notificationEvents->getEvent($email);

        $email->setEventObject($event->getMockEventObject());

        $output = Craft::$app->getView()->renderString($output, [
            'email' => $email,
            'object' => $email->getEventObject()
        ]);

        // Output it into a buffer, in case TasksService wants to close the connection prematurely
        ob_start();

        echo $output;

        // End the request
        Craft::$app->end();
    }

    /**
     * @param NotificationEmail $notificationEmail
     * @param array             $errors
     *
     * @return array
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function getNotificationErrors(NotificationEmail $notificationEmail, array $errors = []): array
    {
        $currentPluginHandle = Craft::$app->request->getSegment(1);

        $notificationEditUrl = UrlHelper::cpUrl($currentPluginHandle.'/notifications/edit/'.$notificationEmail->id);
        $notificationEditSettingsUrl = UrlHelper::cpUrl($currentPluginHandle.'/settings/notifications/edit/'.
            $notificationEmail->id);

        $event = SproutBase::$app->notificationEvents->getEventById($notificationEmail->eventId);

        $emailTemplates = $notificationEmail->getEmailTemplates();

        if ($event === null) {
            $errors[] = Craft::t('sprout-base', 'No Event is selected. <a href="{url}">Edit Notification</a>.', [
                'url' => $notificationEditUrl
            ]);
        }

        if (empty($emailTemplates->getPath())) {
            $errors[] = Craft::t('sprout-base', 'No template found. <a href="{url}">Edit Notification Settings</a>.',
                [
                    'url' => $notificationEditSettingsUrl
                ]);
        }

        if ($errors) {
            return $errors;
        }

        $notificationEmail->setEventObject($event->getMockEventObject());

        /**
         * @var $mailer Mailer
         */
        $mailer = $notificationEmail->getMailer();

        // Process our message to generate any errors on the NotificationEmail element we may see when preparing the send
        $mailer->getMessage($notificationEmail);

        $templateErrors = $notificationEmail->getErrors();

        if (!empty($templateErrors['template'])) {

            foreach ($templateErrors['template'] as $templateError) {
                $errors[] = Craft::t('sprout-base', $templateError);
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
        $notificationEmail->pluginHandle = $currentPluginHandle;
        $notificationEmail->slug = $handle;

        $systemEmailSettings = Craft::$app->getSystemSettings()->getEmailSettings();

        // @todo - add override settings to Sprout Email
        $notificationEmail->fromName = $systemEmailSettings->fromName;
        $notificationEmail->fromEmail = $systemEmailSettings->fromEmail;

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
