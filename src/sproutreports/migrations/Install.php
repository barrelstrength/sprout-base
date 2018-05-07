<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\sproutreports\migrations;

use craft\db\Migration;

class Install extends Migration
{
    private $reportTable = '{{%sproutreports_reports}}';
    private $reportGroupTable = '{{%sproutreports_reportgroups}}';
    private $dataSourcesTable = '{{%sproutreports_datasources}}';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTables();
    }

    public function createTables()
    {
        $reportTable = $this->getDb()->tableExists($this->reportTable);

        if ($reportTable == false) {
            $this->createTable($this->reportTable,
                [
                    'id' => $this->primaryKey(),
                    'dataSourceId' => $this->integer(),
                    'groupId' => $this->integer(),
                    'name' => $this->string()->notNull(),
                    'hasNameFormat' => $this->boolean(),
                    'nameFormat' => $this->string(),
                    'handle' => $this->string()->notNull(),
                    'description' => $this->text(),
                    'allowHtml' => $this->boolean(),
                    'settings' => $this->text(),
                    'enabled' => $this->boolean(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid()
                ]
            );

            $this->createIndex($this->db->getIndexName($this->reportTable, 'handle', true, true),
                $this->reportTable, 'handle', true);

            $this->createIndex($this->db->getIndexName($this->reportTable, 'name', true, true),
                $this->reportTable, 'name', true);
        }

        $reportGroupTable = $this->getDb()->tableExists($this->reportGroupTable);

        if ($reportGroupTable == false) {
            $this->createTable($this->reportGroupTable, [
                'id' => $this->primaryKey(),
                'name' => $this->string()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]);

            $this->createIndex($this->db->getIndexName($this->reportGroupTable, 'name', false, true),
                $this->reportGroupTable, 'name', false);
        }

        $dataSourcesTable = $this->getDb()->tableExists($this->dataSourcesTable);

        if ($dataSourcesTable == false) {
            $this->createTable($this->dataSourcesTable, [
                'id' => $this->primaryKey(),
                'type' => $this->string(),
                'allowNew' => $this->boolean(),
                'pluginId' => $this->string(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]);
        }
    }

    public function dropTables()
    {
        $reportTable = $this->getDb()->tableExists($this->reportTable);

        if ($reportTable) {
            $this->dropTable($this->reportTable);
        }

        $reportGroupTable = $this->getDb()->tableExists($this->reportGroupTable);

        if ($reportGroupTable) {
            $this->dropTable($this->reportGroupTable);
        }

        $dataSourcesTable = $this->getDb()->tableExists($this->dataSourcesTable);

        if ($dataSourcesTable) {
            $this->dropTable($this->dataSourcesTable);
        }
    }
}