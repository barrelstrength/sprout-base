<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\redirects\records;

use craft\db\ActiveRecord;
use craft\records\Element;
use DateTime;
use yii\db\ActiveQueryInterface;

/**
 * @property int                  $id
 * @property string               $oldUrl
 * @property string               $newUrl
 * @property int                  $method
 * @property bool                 $matchStrategy
 * @property ActiveQueryInterface $element
 * @property int                  $count
 * @property string               $lastRemoteIpAddress
 * @property string               $lastReferrer
 * @property string               $lastUserAgent
 * @property DateTime             $dateLastUsed
 *
 */
class Redirect extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutseo_redirects}}';
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
