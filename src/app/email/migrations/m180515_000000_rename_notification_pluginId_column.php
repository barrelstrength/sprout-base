<?php

namespace barrelstrength\sproutbase\app\email\migrations;

use craft\db\Migration;

/**
 * m180515_000000_rename_notification_pluginId_column migration.
 */
class m180515_000000_rename_notification_pluginId_column extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutemail_notificationemails}}';

        // This migration isn't relevant to most users, this was a minor change during beta development
        if ($this->db->columnExists($table, 'pluginId')) {
            if (!$this->db->columnExists($table, 'pluginHandle')) {
                $this->renameColumn($table, 'pluginId', 'pluginHandle');
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180515_000000_rename_notification_pluginId_column cannot be reverted.\n";
        return false;
    }
}
