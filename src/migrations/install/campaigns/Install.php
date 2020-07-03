<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\migrations\install\campaigns;

use barrelstrength\sproutbase\app\campaigns\elements\CampaignEmail;
use barrelstrength\sproutbase\app\campaigns\records\CampaignEmail as CampaignEmailRecord;
use barrelstrength\sproutbase\app\campaigns\records\CampaignType as CampaignTypeRecord;
use craft\db\Migration;
use craft\db\Table;

class Install extends Migration
{
    public function safeUp()
    {
        if (!$this->getDb()->tableExists(CampaignTypeRecord::tableName())) {
            $this->createTable(CampaignTypeRecord::tableName(), [
                'id' => $this->primaryKey(),
                'name' => $this->string()->notNull(),
                'handle' => $this->string()->notNull(),
                'mailer' => $this->string()->notNull(),
                'emailTemplateId' => $this->string(),
                'titleFormat' => $this->string(),
                'urlFormat' => $this->string(),
                'hasUrls' => $this->boolean(),
                'hasAdvancedTitles' => $this->boolean(),
                'template' => $this->string(),
                'templateCopyPaste' => $this->string(),
                'fieldLayoutId' => $this->integer(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }

        if (!$this->getDb()->tableExists(CampaignEmailRecord::tableName())) {
            $this->createTable(CampaignEmailRecord::tableName(), [
                'id' => $this->primaryKey(),
                'subjectLine' => $this->string()->notNull(),
                'campaignTypeId' => $this->integer()->notNull(),
                'recipients' => $this->text(),
                'emailSettings' => $this->text(),
                'defaultBody' => $this->text(),
                'listSettings' => $this->text(),
                'fromName' => $this->string(),
                'fromEmail' => $this->string(),
                'replyToEmail' => $this->string(),
                'enableFileAttachments' => $this->boolean(),
                'dateScheduled' => $this->dateTime(),
                'dateSent' => $this->dateTime(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            $this->addForeignKey(null, CampaignEmailRecord::tableName(), ['id'], Table::ELEMENTS, ['id'], 'CASCADE');
        }
    }

    public function safeDown()
    {
        // Delete Notification Email Elements
        $this->delete(Table::ELEMENTS, ['type' => CampaignEmail::class]);

        $this->dropTableIfExists('{{%sproutemail_campaigntypes}}');
        $this->dropTableIfExists('{{%sproutemail_campaignemails}}');
//        $this->dropTableIfExists('{{%sproutemail_mailers}}');
    }
}