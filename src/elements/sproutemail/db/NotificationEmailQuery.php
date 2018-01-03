<?php

namespace barrelstrength\sproutbase\elements\sproutemail\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class NotificationEmailQuery extends ElementQuery
{
	/**
	 * @inheritdoc
	 */
	protected function beforePrepare(): bool
	{
		$this->joinElementTable('sproutemail_notificationemails');
		$this->query->select([
			'sproutemail_notificationemails.name',
			'sproutemail_notificationemails.template',
			'sproutemail_notificationemails.eventId',
			'sproutemail_notificationemails.options',
			'sproutemail_notificationemails.subjectLine',
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

		return parent::beforePrepare();
	}
}