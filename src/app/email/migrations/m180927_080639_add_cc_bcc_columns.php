<?php

namespace barrelstrength\sproutbase\app\email\migrations;

use craft\db\Migration;

/**
 * m180927_080639_add_cc_bcc_columns migration.
 */
class m180927_080639_add_cc_bcc_columns extends Migration
{
    /**
     * @return bool|void
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp()
    {
        $table = '{{%sproutemail_notificationemails}}';

        if (!$this->db->columnExists($table, 'cc')) {
            $this->addColumn($table, 'cc', $this->string()->after('recipients'));
        }

        if (!$this->db->columnExists($table, 'bcc')) {
            $this->addColumn($table, 'bcc', $this->string()->after('recipients'));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180927_080639_add_cc_bcc_columns cannot be reverted.\n";
        return false;
    }
}
