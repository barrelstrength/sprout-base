<?php

namespace barrelstrength\sproutbase\app\email\services;

use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\base\NotificationEmailSenderInterface;
use barrelstrength\sproutbase\app\email\base\NotificationEvent;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use barrelstrength\sproutbase\app\email\records\NotificationEmail as NotificationEmailRecord;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\errors\ElementNotFoundException;
use craft\helpers\App;
use craft\helpers\ElementHelper;
use craft\models\FieldLayout;
use Exception;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\ExitException;

class NotificationEmails extends Component
{
    /**
     * @param NotificationEmail $notificationEmail
     *
     * @return bool
     * @throws Throwable
     */
    public function saveNotification(NotificationEmail $notificationEmail)
    {
        if (!$notificationEmail->validate(null, false)) {
            Craft::info('Notification Email not saved due to validation error.', __METHOD__);

            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            // Save the Field Layout
            /* @var FieldLayout $fieldLayout */
            $fieldLayout = $notificationEmail->getFieldLayout();
            Craft::$app->getFields()->saveLayout($fieldLayout);
            $notificationEmail->fieldLayoutId = $fieldLayout->id;

            // Save the global set
            if (!Craft::$app->getElements()->saveElement($notificationEmail, false)) {
                return false;
            }

            $transaction->commit();

            return true;
        } catch (Throwable $e) {
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
     * @throws Throwable
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
     * @param NotificationEmail $notificationEmail
     *
     * @return bool
     * @throws Throwable
     */
    public function sendNotificationViaMailer(NotificationEmail $notificationEmail)
    {
        try {
            /** @var Mailer|NotificationEmailSenderInterface $mailer */
            $mailer = $notificationEmail->getMailer();

            return $mailer->sendNotificationEmail($notificationEmail);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Retrieves a rendered Notification Email to be shared or for Live Preview
     *
     * @param      $notificationId
     * @param null $type
     *
     * @throws ExitException
     * @throws LoaderError
     * @throws SyntaxError
     * @throws \yii\base\Exception
     * @throws RuntimeError
     */
    public function getPreviewNotificationEmailById($notificationId, $type = null)
    {
        $notificationEmail = Craft::$app->getElements()->getElementById($notificationId, NotificationEmail::class);

        if (!$notificationEmail instanceof NotificationEmail) {
            throw new ElementNotFoundException('Notification Email not found using id '.$$notificationId);
        }

        $event = SproutBase::$app->notificationEvents->getEvent($notificationEmail);

        if (!$event) {
            ob_start();
            echo Craft::t('sprout', 'Notification Email cannot display. The Event setting must be set.');
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
     * @param string $fileExtension
     *
     * @throws ExitException
     * @throws LoaderError
     * @throws SyntaxError
     * @throws \yii\base\Exception
     * @throws RuntimeError
     */
    public function showPreviewEmail(NotificationEmail $email, $fileExtension = 'html')
    {
        if ($fileExtension == 'txt') {
            $output = $email->getEmailTemplates()->getTextBody();
        } else {
            $output = $email->getEmailTemplates()->getHtmlBody();
        }

        /* @var NotificationEvent $event */
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
     * @param string $subjectLine
     * @param string $handle
     *
     * @return NotificationEmail|null
     * @throws Throwable
     */
    public function createNewNotification($subjectLine = null, $handle = null)
    {
        $notificationEmail = new NotificationEmail();
        $subjectLine = $subjectLine ?? Craft::t('sprout', 'Notification');
        $handle = $handle ?? ElementHelper::createSlug($subjectLine);

        $subjectLine = $this->getFieldAsNew('subjectLine', $subjectLine);

        $notificationEmail->title = $subjectLine;
        $notificationEmail->subjectLine = $subjectLine;
        $notificationEmail->slug = $handle;

        $systemEmailSettings = App::mailSettings();

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
