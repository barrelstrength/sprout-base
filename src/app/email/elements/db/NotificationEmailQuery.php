<?php

namespace barrelstrength\sproutbase\app\email\elements\db;


use craft\elements\db\ElementQuery;
use craft\base\Element;
use Craft;

class NotificationEmailQuery extends ElementQuery
{
    public $base;

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('sproutemail_notificationemails');
        $this->query->select([
            'sproutemail_notificationemails.pluginHandle',
            'sproutemail_notificationemails.titleFormat',
            'sproutemail_notificationemails.emailTemplateId',
            'sproutemail_notificationemails.eventId',
            'sproutemail_notificationemails.settings',
            'sproutemail_notificationemails.subjectLine',
            'sproutemail_notificationemails.defaultBody',
            'sproutemail_notificationemails.recipients',
            'sproutemail_notificationemails.listSettings',
            'sproutemail_notificationemails.fromName',
            'sproutemail_notificationemails.fromEmail',
            'sproutemail_notificationemails.replyToEmail',
            'sproutemail_notificationemails.enableFileAttachments',
            'sproutemail_notificationemails.dateCreated',
            'sproutemail_notificationemails.dateUpdated',
            'sproutemail_notificationemails.fieldLayoutId'
        ]);

        $pluginHandle = Craft::$app->request->getBodyParam('criteria.base');

        // Displays all notification event on sprout-email plugin and filters on plugin integration
        if ($pluginHandle != null && $pluginHandle != 'sprout-email') {
            $this->query->where(['sproutemail_notificationemails.pluginHandle' => $pluginHandle]);
        }

        return parent::beforePrepare();
    }

    /**
     * @inheritdoc
     */
    protected function statusCondition(string $status)
    {
        $currentPluginHandle = Craft::$app->getRequest()->getSegment(1);

        /**
         * To show disabled notification emails on integrated plugins
         */
        if ($currentPluginHandle != 'sprout-email') {
            return ['elements.enabled' => ['0', '1']];
        }

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