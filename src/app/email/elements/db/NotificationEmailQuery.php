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
        $this->joinElementTable('sprout_notificationemails');

        $this->query->select([
            'sprout_notificationemails.titleFormat',
            'sprout_notificationemails.emailTemplateId',
            'sprout_notificationemails.eventId',
            'sprout_notificationemails.settings',
            'sprout_notificationemails.sendRule',
            'sprout_notificationemails.subjectLine',
            'sprout_notificationemails.defaultBody',
            'sprout_notificationemails.sendMethod',
            'sprout_notificationemails.recipients',
            'sprout_notificationemails.cc',
            'sprout_notificationemails.bcc',
            'sprout_notificationemails.listSettings',
            'sprout_notificationemails.fromName',
            'sprout_notificationemails.fromEmail',
            'sprout_notificationemails.replyToEmail',
            'sprout_notificationemails.enableFileAttachments',
            'sprout_notificationemails.dateCreated',
            'sprout_notificationemails.dateUpdated',
            'sprout_notificationemails.fieldLayoutId',
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