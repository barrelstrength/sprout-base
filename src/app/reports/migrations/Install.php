<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\migrations;

use barrelstrength\sproutbase\app\reports\elements\Report;
use barrelstrength\sproutbase\app\reports\models\Settings as SproutBaseReportsSettings;
use barrelstrength\sproutbase\app\reports\records\DataSource as DataSourceRecord;
use barrelstrength\sproutbase\app\reports\records\Report as ReportRecord;
use barrelstrength\sproutbase\app\reports\records\ReportGroup as ReportGroupRecord;
use barrelstrength\sproutbase\migrations\Install as SproutBaseInstall;
use barrelstrength\sproutbase\records\Settings as SproutBaseSettingsRecord;
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

        if (!$this->getDb()->tableExists(ReportRecord::tableName())) {
            $this->createTable(ReportRecord::tableName(),
                [
                    'id' => $this->primaryKey(),
                    'dataSourceId' => $this->integer(),
                    'groupId' => $this->integer(),
                    'name' => $this->string()->notNull(),
                    'hasNameFormat' => $this->boolean(),
                    'nameFormat' => $this->string(),
                    'handle' => $this->string()->notNull(),
                    'description' => $this->text(),
                    'allowHtml' => $this->boolean(),
                    'sortOrder' => $this->string(),
                    'sortColumn' => $this->string(),
                    'delimiter' => $this->string(),
                    'emailColumn' => $this->string(),
                    'settings' => $this->text(),
                    'enabled' => $this->boolean(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid()
                ]
            );

            $this->addForeignKey(null, ReportRecord::tableName(), ['id'], '{{%elements}}', ['id'], 'CASCADE');

            $this->createIndex($this->db->getIndexName(ReportRecord::tableName(), 'handle', true, true),
                ReportRecord::tableName(), 'handle', true);

            $this->createIndex($this->db->getIndexName(ReportRecord::tableName(), 'name', true, true),
                ReportRecord::tableName(), 'name', true);
        }

        if (!$this->getDb()->tableExists(ReportGroupRecord::tableName())) {
            $this->createTable(ReportGroupRecord::tableName(), [
                'id' => $this->primaryKey(),
                'name' => $this->string()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]);

            $this->createIndex(
                $this->db->getIndexName(ReportGroupRecord::tableName(), 'name', false, true),
                ReportGroupRecord::tableName(),
                'name'
            );
        }

        if (!$this->getDb()->tableExists(DataSourceRecord::tableName())) {
            $this->createTable(DataSourceRecord::tableName(), [
                'id' => $this->primaryKey(),
                'type' => $this->string(),
                'allowNew' => $this->boolean(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]);
        }

        $this->insertDefaultSettings();
    }

    public function safeDown()
    {
        // Delete Report Elements
        $this->delete(Table::ELEMENTS, ['type' => Report::class]);

        $this->dropTableIfExists(ReportRecord::tableName());
        $this->dropTableIfExists(ReportGroupRecord::tableName());
        $this->dropTableIfExists(DataSourceRecord::tableName());

        $this->removeSharedSettings();
    }

    public function insertDefaultSettings()
    {
        $settingsRow = (new Query())
            ->select(['*'])
            ->from([SproutBaseSettingsRecord::tableName()])
            ->where(['model' => SproutBaseReportsSettings::class])
            ->one();

        if ($settingsRow === null) {

            $settings = new SproutBaseReportsSettings();

            $settingsArray = [
                'model' => SproutBaseReportsSettings::class,
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
            ->where(['model' => SproutBaseReportsSettings::class])
            ->exists();

        if ($settingsExist) {
            $this->delete(SproutBaseSettingsRecord::tableName(), [
                'model' => SproutBaseReportsSettings::class
            ]);
        }
    }
}