<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\records;

use craft\db\ActiveRecord;

/**
 * Class EntryStatus record
 *
 * @property int $id     ID
 * @property string $cpEditUrl
 * @property string $name   Name
 * @property string $handle Handle
 * @property string $color
 * @property int $sortOrder
 * @property bool $isDefault
 */
class EntryStatus extends ActiveRecord
{
    /**
     * @inheritDoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sprout_formentries_statuses}}';
    }
}