<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\records;

use craft\db\ActiveRecord;

/**
 * @property $entryId
 * @property $type
 */
class EntriesSpamLog extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%sprout_formentries_spam_log}}';
    }
}