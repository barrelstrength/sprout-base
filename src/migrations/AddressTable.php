<?php
namespace barrelstrength\sproutcore\migrations;

use craft\db\Migration;

class AddressTable extends Migration
{
	// Properties
	// =========================================================================

	/**
	 * @var string|null The table name
	 */
	public $tableName = '{{%sproutcore_addresses}}';

	// Public Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$response = $this->getDb()->tableExists($this->tableName);

		if ($response == false)
		{
			$this->createTable($this->tableName, [
				'id'                => $this->primaryKey(),
				'modelId'           => $this->integer(),
				'countryCode'       => $this->string(),
				'locality'          => $this->string(),
				'dependentLocality' => $this->string(),
				'postalCode'        => $this->string(),
				'sortingCode'       => $this->string(),
				'address1'          => $this->string(),
				'address2'          => $this->string(),
				'dateCreated'       => $this->dateTime()->notNull(),
				'dateUpdated'       => $this->dateTime()->notNull(),
				'uid'               => $this->uid(),
			]);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		return false;
	}
}
