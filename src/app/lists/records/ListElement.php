<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\records;

use barrelstrength\sproutbase\app\lists\records\Subscription as SubscriptionRecord;
use craft\base\Element;
use craft\db\ActiveRecord;
use yii\base\InvalidConfigException;
use yii\db\ActiveQueryInterface;

/**
 * Class ListElement record.
 *
 * @property int $id
 * @property int $elementId
 * @property string $type
 * @property string $name
 * @property string $handle
 * @property ActiveQueryInterface $element
 * @property ActiveQueryInterface $subscribers
 * @property ActiveQueryInterface $listsWithSubscribers
 * @property int $count
 */
class ListElement extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sprout_lists}}';
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

    /**
     * @return ActiveQueryInterface
     * @throws InvalidConfigException
     */
    public function getSubscribers(): ActiveQueryInterface
    {
        return $this->hasMany(Subscriber::class, ['id' => 'itemId'])
            ->viaTable(SubscriptionRecord::tableName(), ['listId' => 'id']);
    }
}
