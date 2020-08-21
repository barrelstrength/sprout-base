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
        'sprout_sentemail.dateCreated' => SORT_DESC,
    ];

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('sprout_sentemail');

        $this->query->select([
            'sprout_sentemail.id',
            'sprout_sentemail.title',
            'sprout_sentemail.emailSubject',
            'sprout_sentemail.fromEmail',
            'sprout_sentemail.fromName',
            'sprout_sentemail.toEmail',
            'sprout_sentemail.body',
            'sprout_sentemail.htmlBody',
            'sprout_sentemail.info',
            'sprout_sentemail.status',
            'sprout_sentemail.dateCreated',
            'sprout_sentemail.dateUpdated',
        ]);

        return parent::beforePrepare();
    }
}