<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\records\sproutreports;

use craft\db\ActiveRecord;

/**
 * Class Report
 *
 *
 * @property int    $id
 * @property string $name
 * @property bool   $hasNameFormat
 * @property string $nameFormat
 * @property string $handle
 * @property string $description
 * @property bool   $allowHtml
 * @property string $settings
 * @property int    $dataSourceId
 * @property bool   $enabled
 * @property int    $groupId
 */
class Report extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutreports_reports}}';
    }
}