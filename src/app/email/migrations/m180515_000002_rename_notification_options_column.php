<?php

namespace barrelstrength\sproutbase\app\email\migrations;

use craft\db\Migration;

/**
 * m180515_000002_rename_notification_options_column migration.
 */
class m180515_000002_rename_notification_options_column extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp()
    {
        $table = '{{%sproutemail_notificationemails}}';

        if ($this->db->columnExists($table, 'options')) {
            $this->renameColumn($table, 'options', 'settings');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180515_000002_rename_notification_options_column cannot be reverted.\n";
        return false;
    }
}
