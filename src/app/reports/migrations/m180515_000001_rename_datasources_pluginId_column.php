<?php

namespace barrelstrength\sproutbase\app\reports\migrations;

use craft\db\Migration;

/**
 * m180515_000000_rename_datasources_pluginId_column migration.
 */
class m180515_000001_rename_datasources_pluginId_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $table = '{{%sproutreports_datasources}}';

        if ($this->db->columnExists($table, 'pluginId') && !$this->db->columnExists($table, 'pluginHandle')) {
            $this->renameColumn($table, 'pluginId', 'pluginHandle');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180515_000000_rename_datasources_pluginId_column cannot be reverted.\n";
        return false;
    }
}
