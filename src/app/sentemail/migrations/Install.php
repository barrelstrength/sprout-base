<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\sentemail\migrations;

use barrelstrength\sproutbase\records\Settings as SproutBaseSettingsRecord;
use barrelstrength\sproutbase\app\sentemail\elements\SentEmail;
use barrelstrength\sproutbase\app\sentemail\models\Settings;
use barrelstrength\sproutbase\app\sentemail\models\Settings as SproutSentEmailSettings;
use barrelstrength\sproutbase\app\sentemail\records\SentEmail as SentEmailRecord;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;

class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->getDb()->tableExists(SentEmailRecord::tableName())) {
            $this->createTable(SentEmailRecord::tableName(),
                [
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
                ]
            );

            $this->addForeignKey(null, SentEmailRecord::tableName(),
                ['id'], Table::ELEMENTS, ['id'], 'CASCADE');
        }

        $this->insertDefaultSettings();
    }

    public function safeDown()
    {
        // Delete Sent Email Elements
        $this->delete(Table::ELEMENTS, ['type' => SentEmail::class]);

        // Delete Sent Email Table
        $this->dropTableIfExists(SentEmailRecord::tableName());
        $this->removeSharedSettings();
    }

    public function insertDefaultSettings()
    {
        $settingsRow = (new Query())
            ->select(['*'])
            ->from([SproutBaseSettingsRecord::tableName()])
            ->where(['model' => SproutSentEmailSettings::class])
            ->one();

        if ($settingsRow === null) {

            $settings = new Settings();
            $settings->enableSentEmails = '1';

            $settingsArray = [
                'model' => SproutSentEmailSettings::class,
                'settings' => json_encode($settings->toArray())
            ];

            $this->insert(SproutBaseSettingsRecord::tableName(), $settingsArray);
        }
    }

    public function removeSharedSettings()
    {
        $settingsExist = (new Query())
            ->select(['*'])
            ->from([SproutBaseSettingsRecord::tableName()])
            ->where(['model' => SproutSentEmailSettings::class])
            ->exists();

        if ($settingsExist) {
            $this->delete(SproutBaseSettingsRecord::tableName(), [
                'model' => SproutSentEmailSettings::class
            ]);
        }
    }
}