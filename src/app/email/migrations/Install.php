<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\email\migrations;

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
                    'pluginHandle' => $this->string(),
                    'titleFormat' => $this->string(),
                    'emailTemplateId' => $this->string(),
                    'eventId' => $this->string(),
                    'settings' => $this->text(),
                    'subjectLine' => $this->string()->notNull(),
                    'defaultBody' => $this->text(),
                    'recipients' => $this->string(),
                    'cc' => $this->string(),
                    'bcc' => $this->string(),
                    'listSettings' => $this->text(),
                    'fromName' => $this->string(),
                    'fromEmail' => $this->string(),
                    'replyToEmail' => $this->string(),
                    'singleEmail' => $this->boolean(),
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