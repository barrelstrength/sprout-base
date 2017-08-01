<?php

namespace barrelstrength\sproutcore\migrations\sproutreports;

use craft\db\Query;

class Migration extends \craft\db\Migration
{
	private $reportTable      = '{{%sproutreports_report}}';
	private $reportGroupTable = '{{%sproutreports_reportgroups}}';
	private $dataSourcesTable = '{{%sproutreports_datasources}}';

	public function createTables()
	{
		$reportTable = $this->getDb()->tableExists($this->reportTable);

		if ($reportTable == false)
		{
			$this->createTable($this->reportTable,
				[
					'id'     => $this->primaryKey(),
					'name'   => $this->string()->notNull(),
					'handle' => $this->string()->notNull(),
					'description'  => $this->text(),
					'allowHtml'    => $this->boolean(),
					'options'      => $this->text(),
					'dataSourceId' => $this->string(),
					'groupId'      => $this->integer(),
					'enabled'      => $this->boolean(),
					'dateCreated'  => $this->dateTime()->notNull(),
					'dateUpdated'  => $this->dateTime()->notNull(),
					'uid'          => $this->uid()
				]
			);

			$this->createIndex($this->db->getIndexName($this->reportTable, 'handle', true, true),
				$this->reportTable, 'name', true);

			$this->createIndex($this->db->getIndexName($this->reportTable, 'name', true, true),
				$this->reportTable, 'name', true);

			$this->createIndex($this->db->getIndexName($this->reportTable, 'dataSourceId', true, false),
				$this->reportTable, 'dataSourceId', false);
		}

		$reportGroupTable = $this->getDb()->tableExists($this->reportGroupTable);

		if ($reportGroupTable == false)
		{
			$this->createTable($this->reportGroupTable, [
				'id'          => $this->primaryKey(),
				'name'        => $this->string()->notNull(),
				'dateCreated' => $this->dateTime()->notNull(),
				'dateUpdated' => $this->dateTime()->notNull(),
				'uid'         => $this->uid()
			]);

			$this->createIndex($this->db->getIndexName($this->reportGroupTable, 'name', false, true),
				$this->reportGroupTable, 'name', false);
		}

		$dataSourcesTable = $this->getDb()->tableExists($this->dataSourcesTable);

		if ($dataSourcesTable == false)
		{
			$this->createTable($this->dataSourcesTable, [
				'id'           => $this->primaryKey(),
				'dataSourceId' => $this->string(),
				'options'      => $this->text(),
				'allowNew'     => $this->boolean(),
				'dateCreated'  => $this->dateTime()->notNull(),
				'dateUpdated'  => $this->dateTime()->notNull(),
				'uid'          => $this->uid()
			]);
		}
	}

	public function dropTablesByDataSourceId($dataSourceId)
	{
		$query = new Query();
		$query->createCommand()
			->delete('sproutreports_report', ['dataSourceId' => $dataSourceId])
			->execute();
	}

	public function dropTables()
	{
		$reportTable = $this->getDb()->tableExists($this->reportTable);

		if ($reportTable)
		{
			$this->dropTable($this->reportTable);
		}

		$reportGroupTable = $this->getDb()->tableExists($this->reportGroupTable);

		if ($reportGroupTable)
		{
			$this->dropTable($this->reportGroupTable);
		}

		$dataSourcesTable = $this->getDb()->tableExists($this->dataSourcesTable);

		if ($dataSourcesTable)
		{
			$this->dropTable($this->dataSourcesTable);
		}
	}
}