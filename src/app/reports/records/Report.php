<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\records;

use craft\base\Element;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * @property int                  $id
 * @property string               $name
 * @property bool                 $hasNameFormat
 * @property string               $nameFormat
 * @property string               $handle
 * @property string               $description
 * @property bool                 $allowHtml
 * @property string               $sortOrder
 * @property string               $sortColumn
 * @property string               $delimiter
 * @property string               $emailColumn
 * @property string               $settings
 * @property int                  $dataSourceId
 * @property bool                 $enabled
 * @property ActiveQueryInterface $element
 * @property int                  $groupId
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

    /**
     * Returns the entry’s element.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }
}