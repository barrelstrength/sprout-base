<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\sentemail\elements\db;

use barrelstrength\sproutbase\app\sentemail\records\SentEmail as SentEmailRecord;
use craft\elements\db\ElementQuery;

class SentEmailQuery extends ElementQuery
{
    protected $defaultOrderBy = [
        'sprout_sent_emails.dateCreated' => SORT_DESC,
    ];

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('sprout_sent_emails');

        $this->query->select([
            'sprout_sent_emails.id',
            'sprout_sent_emails.title',
            'sprout_sent_emails.emailSubject',
            'sprout_sent_emails.fromEmail',
            'sprout_sent_emails.fromName',
            'sprout_sent_emails.toEmail',
            'sprout_sent_emails.body',
            'sprout_sent_emails.htmlBody',
            'sprout_sent_emails.info',
            'sprout_sent_emails.status',
            'sprout_sent_emails.dateCreated',
            'sprout_sent_emails.dateUpdated',
        ]);

        return parent::beforePrepare();
    }
}