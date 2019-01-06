<?php

namespace barrelstrength\sproutbase\app\reports\migrations;

use craft\db\Migration;
use craft\db\Query;

/**
 * m180307_042132_craft3_schema_changes migration.
 */
class m180307_042132_craft3_schema_changes extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp(): bool
    {
        // Update Reports Table columns
        if (!$this->db->columnExists('{{%sproutreports_reports}}', 'hasNameFormat')) {
            $this->addColumn('{{%sproutreports_reports}}', 'hasNameFormat', $this->integer()->after('name'));
        }

        if (!$this->db->columnExists('{{%sproutreports_reports}}', 'nameFormat')) {
            $this->addColumn('{{%sproutreports_reports}}', 'nameFormat', $this->string()->after('name'));
        }

        if (!$this->db->columnExists('{{%sproutreports_reports}}', 'settings')) {
            $this->renameColumn('{{%sproutreports_reports}}', 'options', 'settings');
        }

        // Update Data Source Table columns
        if (!$this->db->columnExists('{{%sproutreports_datasources}}', 'type')) {
            $this->renameColumn('{{%sproutreports_datasources}}', 'dataSourceId', 'type');
        }

        /** @noinspection ClassConstantCanBeUsedInspection */
        $dataSourcesMap = [
            'sproutreports.query' => 'barrelstrength\sproutreports\integrations\sproutreports\datasources\CustomQuery',
            'sproutreports.twig' => 'barrelstrength\sproutreports\integrations\sproutreports\datasources\CustomTwigTemplate'
        ];

        // Update our Data Source records and related IDs in the Reports table
        foreach ($dataSourcesMap as $oldDataSourceId => $dataSourceClass) {

            $query = new Query();

            // See if our old data source exists
            $dataSource = $query->select('*')
                ->from(['{{%sproutreports_datasources}}'])
                ->where(['type' => $oldDataSourceId])
                ->one();

            if ($dataSource === null) {
                // If not, see if our new Data Source exists
                $dataSource = $query->select('*')
                    ->from(['{{%sproutreports_datasources}}'])
                    ->where(['type' => $dataSourceClass])
                    ->one();
            }

            // If we don't have a Data Source record, add it
            if ($dataSource === null) {
                $this->insert('{{%sproutreports_datasources}}', [
                    'type' => $dataSourceClass,
                    'allowNew' => 1
                ]);
                $dataSource['id'] = $this->db->getLastInsertID('{{%sproutreports_datasources}}');
                $dataSource['allowNew'] = 1;
            }

            // Update our existing or new Data Source
            $this->update('{{%sproutreports_datasources}}', [
                'type' => $dataSourceClass,
                'allowNew' => $dataSource['allowNew'] ?? 1
            ], [
                'id' => $dataSource['id']
            ], [], false);

            // Update any related dataSourceIds in our Reports table
            $this->update('{{%sproutreports_reports}}', [
                'dataSourceId' => $dataSource['id']
            ], [
                'dataSourceId' => $oldDataSourceId
            ], [], false);
        }

        // Remove Data Source Table columns
        if ($this->db->columnExists('{{%sproutreports_datasources}}', 'options')) {
            $this->dropColumn('{{%sproutreports_datasources}}', 'options');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180307_042132_craft3_schema_changes cannot be reverted.\n";
        return false;
    }
}
