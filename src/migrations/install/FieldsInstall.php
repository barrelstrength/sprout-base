<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\migrations\install;

use barrelstrength\sproutbase\app\fields\records\Address as AddressRecord;
use craft\db\Migration;

class FieldsInstall extends Migration
{
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
