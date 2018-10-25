<?php

namespace barrelstrength\sproutbase\app\email\migrations;

use Craft;
use craft\db\Migration;

/**
 * m180725_080639_add_notification_columns migration.
 */
class m180501_000003_add_notification_columns extends Migration
{
    /**
     * @return bool|void
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp()
    {
        // Craft 2 updates
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

        // Sort out Title Format

        // If we have a name column and haven't yet created the titleFormat column
        // then let's create it.
        if ($this->db->columnExists($table, 'name') &&
            !$this->db->columnExists($table, 'titleFormat')) {
            $this->renameColumn($table, 'name', 'titleFormat');
        }

        // If we have a name column and titleFormat column, we may have accidentally created
        // the titleFormat column without removing the name column. Let's go ahead and clean that up.
        if ($this->db->columnExists($table, 'name') &&
            $this->db->columnExists($table, 'titleFormat')) {
            $this->dropColumn($table, 'name');
        }

        // Sort out emailTemplateId column

        // If we have a template column and haven't yet created the emailTemplateId column
        // then let's create it.
        if ($this->db->columnExists($table, 'template') &&
            !$this->db->columnExists($table, 'emailTemplateId')) {
            $this->renameColumn($table, 'template', 'emailTemplateId');
        }

        // If we have a template column and emailTemplateId column, we may have accidentally created
        // the emailTemplateId column without removing the template column. Let's go ahead and clean that up.
        if ($this->db->columnExists($table, 'template') &&
            $this->db->columnExists($table, 'emailTemplateId')) {
            $this->dropColumn($table, 'template');
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
