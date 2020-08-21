<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\seo\records;

use yii\db\ActiveRecord;

class GlobalMetadata extends ActiveRecord
{
    /**
     * @inheritDoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sprout_globalmetadata}}';
    }
}