<?php

namespace barrelstrength\sproutbase\app\email\migrations;

use craft\db\Migration;

/**
 * m181128_000000_add_list_settings_column migration.
 */
class m181128_000000_add_list_settings_column extends Migration
{
    /**
     * @return bool|void
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp()
    {
        $table = '{{%sproutemail_notificationemails}}';

        if (!$this->db->columnExists($table, 'listSettings')) {
            $this->addColumn($table, 'listSettings', $this->string()->after('recipients'));
        }

        $table = '{{%sproutemail_campaignemails}}';

        if (!$this->db->columnExists($table, 'listSettings')) {
            $this->addColumn($table, 'listSettings', $this->string()->after('recipients'));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m181128_000000_add_list_settings_column cannot be reverted.\n";
        return false;
    }
}
