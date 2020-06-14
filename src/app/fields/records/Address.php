<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\records;

use craft\db\ActiveRecord;
use craft\gql\types\DateTime;

/**
 * @property int $id
 * @property int $elementId
 * @property int $siteId
 * @property int $fieldId
 * @property string $countryCode
 * @property string $administrativeAreaCode
 * @property string $locality
 * @property string $dependentLocality
 * @property string $postalCode
 * @property string $sortingCode
 * @property string $address1
 * @property string $address2
 * @property DateTime $dateCreated
 * @property DateTime $dateUpdated
 * @property string $uid
 */
class Address extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sprout_addresses}}';
    }
}
