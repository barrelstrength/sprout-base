<?php

namespace barrelstrength\sproutbase\app\email\services;

use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\base\NotificationEmailSenderInterface;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use barrelstrength\sproutbase\app\email\records\NotificationEmail as NotificationEmailRecord;
use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\helpers\App;
use craft\helpers\ElementHelper;
use craft\models\FieldLayout;
use Exception;
use Throwable;
use yii\db\Transaction;

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

        /** @var Transaction $transaction */
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
            $field => $value,
        ]);
    }
}
