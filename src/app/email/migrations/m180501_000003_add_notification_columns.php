<?php

namespace barrelstrength\sproutbase\app\email\migrations;

use craft\db\Migration;

/**
 * m180725_080639_add_notification_columns migration.
 */
class m180501_000003_add_notification_columns extends Migration
{
    /**
     * @return bool
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp(): bool
    {
        // Craft 2 updates
        $table = '{{%sproutemail_notificationemails}}';

        if (!$this->db->columnExists($table, 'pluginHandle')) {
            $this->addColumn($table, 'pluginHandle', $this->string()->after('id'));
        }

        if (!$this->db->columnExists($table, 'defaultBody')) {
            $this->addColumn($table, 'defaultBody', $this->text()->after('subjectLine'));
        }

        if (!$this->db->columnExists($table, 'singleEmail')) {
            $this->addColumn($table, 'singleEmail', $this->tinyInteger()->after('replyToEmail'));
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

        // Remove NOT NULL to the next columns
        $this->alterColumn($table, 'titleFormat', $this->string());
        $this->alterColumn($table, 'emailTemplateId', $this->string());
        $this->alterColumn($table, 'enableFileAttachments', $this->boolean());

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180725_080639_add_notification_columns cannot be reverted.\n";
        return false;
    }
}
