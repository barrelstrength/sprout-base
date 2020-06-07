<?php

namespace barrelstrength\sproutbase\app\email\elements\db;

use craft\base\Element;
use craft\elements\db\ElementQuery;

class NotificationEmailQuery extends ElementQuery
{
    /**
     * @return bool
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('sproutemail_notificationemails');

        $this->query->select([
            'sproutemail_notificationemails.titleFormat',
            'sproutemail_notificationemails.emailTemplateId',
            'sproutemail_notificationemails.eventId',
            'sproutemail_notificationemails.settings',
            'sproutemail_notificationemails.sendRule',
            'sproutemail_notificationemails.subjectLine',
            'sproutemail_notificationemails.defaultBody',
            'sproutemail_notificationemails.sendMethod',
            'sproutemail_notificationemails.recipients',
            'sproutemail_notificationemails.cc',
            'sproutemail_notificationemails.bcc',
            'sproutemail_notificationemails.listSettings',
            'sproutemail_notificationemails.fromName',
            'sproutemail_notificationemails.fromEmail',
            'sproutemail_notificationemails.replyToEmail',
            'sproutemail_notificationemails.enableFileAttachments',
            'sproutemail_notificationemails.dateCreated',
            'sproutemail_notificationemails.dateUpdated',
            'sproutemail_notificationemails.fieldLayoutId'
        ]);

        return parent::beforePrepare();
    }

    /**
     * @inheritdoc
     */
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