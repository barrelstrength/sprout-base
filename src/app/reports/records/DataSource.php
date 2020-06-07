<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\records;

use craft\db\ActiveRecord;

/**
 * Class DataSource
 *
 * @property int    $id
 * @property int    $pluginId
 * @property string $type
 * @property bool   $allowNew
 *
 * @package barrelstrength\sproutbase\app\reports\records
 */
class DataSource extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutreports_datasources}}';
    }
}