<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\records;

use craft\db\ActiveRecord;

/**
 * Class Subscription record.
 *
 * @property int $id
 * @property int $listId
 * @property int $itemId
 */
class Subscription extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sprout_subscriptions}}';
    }
}
