<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\migrations\sproutemail;

use craft\db\Migration;

class Install extends Migration
{
    private $notificationEmailTable = '{{%sproutemail_notificationemails}}';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTables();
    }

    public function createTables()
    {
        $notificationTable = $this->getDb()->tableExists($this->notificationEmailTable);

        if ($notificationTable == false) {
            $this->createTable($this->notificationEmailTable,
                [
                    'id' => $this->primaryKey(),
                    'pluginId' => $this->string(),
                    'name' => $this->string()->notNull(),
                    'template' => $this->string()->notNull(),
                    'eventId' => $this->string(),
                    'options' => $this->text(),
                    'subjectLine' => $this->string(),
                    'body' => $this->text(),
                    'recipients' => $this->string(),
                    'listSettings' => $this->text(),
                    'fromName' => $this->string(),
                    'fromEmail' => $this->string(),
                    'replyToEmail' => $this->string(),
                    'enableFileAttachments' => $this->boolean(),
                    'dateCreated' => $this->dateTime(),
                    'dateUpdated' => $this->dateTime(),
                    'fieldLayoutId' => $this->integer(),
                    'uid' => $this->uid()
                ]
            );

            $this->addForeignKey(null, $this->notificationEmailTable, ['id'], '{{%elements}}', ['id'], 'CASCADE', null);
        }
    }

    public function dropTables()
    {
        $notificationTable = $this->getDb()->tableExists($this->notificationEmailTable);

        if ($notificationTable) {
            $this->dropTable($this->notificationEmailTable);
        }
    }
}