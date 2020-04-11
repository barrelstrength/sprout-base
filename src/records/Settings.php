<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\records;

use craft\db\ActiveRecord;

class Settings extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sprout_settings}}';
    }
}
