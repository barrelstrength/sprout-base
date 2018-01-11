<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\migrations\sproutfields;

use craft\db\Migration;

class Install extends Migration
{
    // Properties
    // =========================================================================

    /**
     * @var string|null The table name
     */
    public $tableName = '{{%sproutfields_addresses}}';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $response = $this->getDb()->tableExists($this->tableName);

        if ($response == false) {
            $this->createTable($this->tableName, [
                'id' => $this->primaryKey(),
                'modelId' => $this->integer(),
                'countryCode' => $this->string(),
                'administrativeArea' => $this->string(),
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
}
