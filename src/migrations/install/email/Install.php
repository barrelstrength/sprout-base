<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\migrations\install\email;

use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use barrelstrength\sproutbase\app\email\records\NotificationEmail as NotificationEmailRecord;
use craft\db\Migration;
use craft\db\Table;

class Install extends Migration
{
    public function safeUp()
    {
        if (!$this->getDb()->tableExists(NotificationEmailRecord::tableName())) {
            $this->createTable(NotificationEmailRecord::tableName(), [
                'id' => $this->primaryKey(),
                'titleFormat' => $this->string(),
                'emailTemplateId' => $this->string(),
                'eventId' => $this->string(),
                'settings' => $this->text(),
                'sendRule' => $this->text(),
                'subjectLine' => $this->string()->notNull(),
                'defaultBody' => $this->text(),
                'recipients' => $this->text(),
                'cc' => $this->text(),
                'bcc' => $this->text(),
                'listSettings' => $this->text(),
                'fromName' => $this->string(),
                'fromEmail' => $this->string(),
                'replyToEmail' => $this->string(),
                'sendMethod' => $this->string(),
                'enableFileAttachments' => $this->boolean(),
                'dateCreated' => $this->dateTime(),
                'dateUpdated' => $this->dateTime(),
                'fieldLayoutId' => $this->integer(),
                'uid' => $this->uid(),
            ]);

            $this->addForeignKey(null, NotificationEmailRecord::tableName(), ['id'], Table::ELEMENTS, ['id'], 'CASCADE');
        }
    }

    public function safeDown()
    {
        // Delete Notification Email Elements
        $this->delete(Table::ELEMENTS, ['type' => NotificationEmail::class]);

        $this->dropTableIfExists(NotificationEmailRecord::tableName());
    }
}