<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\migrations\sentemail;

use barrelstrength\sproutbase\app\sentemail\elements\SentEmail;
use barrelstrength\sproutbase\app\sentemail\records\SentEmail as SentEmailRecord;
use craft\db\Migration;
use craft\db\Table;

class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->getDb()->tableExists(SentEmailRecord::tableName())) {
            $this->createTable(SentEmailRecord::tableName(), [
                'id' => $this->primaryKey(),
                'title' => $this->string(),
                'emailSubject' => $this->string(),
                'fromEmail' => $this->string(),
                'fromName' => $this->string(),
                'toEmail' => $this->string(),
                'body' => $this->text(),
                'htmlBody' => $this->text(),
                'info' => $this->text(),
                'status' => $this->string(),
                'dateCreated' => $this->dateTime(),
                'dateUpdated' => $this->dateTime(),
                'uid' => $this->uid()
            ]);

            $this->addForeignKey(null, SentEmailRecord::tableName(),
                ['id'], Table::ELEMENTS, ['id'], 'CASCADE');
        }
    }

    public function safeDown()
    {
        // Delete Sent Email Elements
        $this->delete(Table::ELEMENTS, ['type' => SentEmail::class]);

        // Delete Sent Email Table
        $this->dropTableIfExists(SentEmailRecord::tableName());
    }
}