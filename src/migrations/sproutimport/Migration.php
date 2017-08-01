<?php

namespace barrelstrength\sproutcore\migrations\sproutimport;

class Migration extends \craft\db\Migration
{
	private $seedsTable      = '{{%sproutimport_seeds}}';

	public function createTables()
	{
		$seedsTable = $this->getDb()->tableExists($this->seedsTable);

		if ($seedsTable == false)
		{
			$this->createTable($this->seedsTable,
				[
					'id'            => $this->primaryKey(),
					'itemId'        => $this->integer()->notNull(),
					'importerClass' => $this->string()->notNull(),
					'type'          => $this->string(),
					'details'       => $this->string(),
					'dateCreated'   => $this->dateTime()->notNull(),
					'dateUpdated'   => $this->dateTime()->notNull(),
					'uid'           => $this->uid()
				]
			);
		}
	}
}