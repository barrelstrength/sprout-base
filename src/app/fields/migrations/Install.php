<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\migrations;

use barrelstrength\sproutbase\app\fields\records\Address as AddressRecord;
use craft\db\Migration;

class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->getDb()->tableExists(AddressRecord::tableName())) {
            $this->createTable(AddressRecord::tableName(), [
                'id' => $this->primaryKey(),
                'elementId' => $this->integer(),
                'siteId' => $this->integer(),
                'fieldId' => $this->integer(),
                'countryCode' => $this->string(),
                'administrativeAreaCode' => $this->string(),
                'locality' => $this->string(),
                'dependentLocality' => $this->string(),
                'postalCode' => $this->string(),
                'sortingCode' => $this->string(),
                'address1' => $this->string(),
                'address2' => $this->string(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }
    }

    public function safeDown()
    {
        $this->dropTableIfExists(AddressRecord::tableName());
    }
}
