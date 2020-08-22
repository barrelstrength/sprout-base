<?php

namespace barrelstrength\sproutbase\app\email\elements\db;

use barrelstrength\sproutbase\app\email\records\NotificationEmail as NotificationEmailRecord;
use craft\base\Element;
use craft\elements\db\ElementQuery;

class NotificationEmailQuery extends ElementQuery
{
    /**
     * @return bool
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('sprout_notification_emails');

        $this->query->select([
            'sprout_notification_emails.titleFormat',
            'sprout_notification_emails.emailTemplateId',
            'sprout_notification_emails.eventId',
            'sprout_notification_emails.settings',
            'sprout_notification_emails.sendRule',
            'sprout_notification_emails.subjectLine',
            'sprout_notification_emails.defaultBody',
            'sprout_notification_emails.sendMethod',
            'sprout_notification_emails.recipients',
            'sprout_notification_emails.cc',
            'sprout_notification_emails.bcc',
            'sprout_notification_emails.listSettings',
            'sprout_notification_emails.fromName',
            'sprout_notification_emails.fromEmail',
            'sprout_notification_emails.replyToEmail',
            'sprout_notification_emails.enableFileAttachments',
            'sprout_notification_emails.dateCreated',
            'sprout_notification_emails.dateUpdated',
            'sprout_notification_emails.fieldLayoutId',
        ]);

        return parent::beforePrepare();
    }

    protected function statusCondition(string $status)
    {
        switch ($status) {
            case Element::STATUS_ENABLED:
                return ['elements.enabled' => '1'];
            case Element::STATUS_DISABLED:
                return ['elements.enabled' => '0'];
            default:
                return false;
        }
    }
}