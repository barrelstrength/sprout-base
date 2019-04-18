<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbase\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutbasereports\migrations\m180307_042132_craft3_schema_changes as SproutReportsCraft2toCraft3Migration;
use barrelstrength\sproutbasereports\migrations\Install as SproutBaseReportsInstall;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $settingsTable = '{{%sproutbase_settings}}';

        if (!$this->db->tableExists($settingsTable)) {
            $this->createTable($settingsTable, [
                'id' => $this->primaryKey(),
                'model' => $this->string(),
                'settings' => $this->text(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "Install cannot be reverted.\n";
        return false;
    }
}
