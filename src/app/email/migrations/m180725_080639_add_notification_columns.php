<?php

namespace barrelstrength\sproutbase\app\email\migrations;

use Craft;
use craft\db\Migration;

/**
 * m180725_080639_add_notification_columns migration.
 */
class m180725_080639_add_notification_columns extends Migration
{
    /**
     * @return bool|void
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp()
    {
        $table = '{{%sproutemail_notificationemails}}';

        if (!$this->db->columnExists($table, 'defaultBody')) {
            $this->addColumn($table, 'defaultBody', $this->string()->after('id'));
        }

        if (!$this->db->columnExists($table, 'pluginHandle')) {
            $this->addColumn($table, 'pluginHandle', $this->string()->after('id'));
        }

        if (!$this->db->columnExists($table, 'singleEmail')) {
            $this->addColumn($table, 'singleEmail', $this->string()->after('id'));
        }

        if (!$this->db->columnExists($table, 'emailTemplateId')) {
            $this->addColumn($table, 'emailTemplateId', $this->string()->after('id'));
        }

        if (!$this->db->columnExists($table, 'titleFormat')) {
            $this->addColumn($table, 'titleFormat', $this->string()->after('id'));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180725_080639_add_notification_columns cannot be reverted.\n";
        return false;
    }
}
