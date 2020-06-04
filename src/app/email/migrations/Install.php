<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\email\migrations;

use barrelstrength\sproutbase\migrations\Install as SproutBaseInstall;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use barrelstrength\sproutbase\app\email\records\NotificationEmail as NotificationEmailRecord;
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
        $migration = new SproutBaseInstall();
        ob_start();
        $migration->safeUp();
        ob_end_clean();

        $notificationTableName = NotificationEmailRecord::tableName();

        if (!$this->getDb()->tableExists($notificationTableName)) {
            $this->createTable($notificationTableName,
                [
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
                    'uid' => $this->uid()
                ]
            );

            $this->addForeignKey(null, $notificationTableName, ['id'], Table::ELEMENTS, ['id'], 'CASCADE');
        }

        $this->insertDefaultSettings();
    }

    public function safeDown()
    {
        // Delete Notification Email Elements
        $this->delete(Table::ELEMENTS, ['type' => NotificationEmail::class]);

        $this->dropTableIfExists(NotificationEmailRecord::tableName());
        $this->removeSharedSettings();
    }

    public function insertDefaultSettings()
    {
        $settingsRow = (new Query())
            ->select(['*'])
            ->from([SproutBaseSettingsRecord::tableName()])
            ->where(['model' => SproutBaseEmailSettings::class])
            ->one();

        if ($settingsRow === null) {

            $settings = new SproutBaseEmailSettings();

            $settingsArray = [
                'model' => SproutBaseEmailSettings::class,
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
            ->where(['model' => SproutBaseEmailSettings::class])
            ->exists();

        if ($settingsExist) {
            $this->delete(SproutBaseSettingsRecord::tableName(), [
                'model' => SproutBaseEmailSettings::class
            ]);
        }
    }
}