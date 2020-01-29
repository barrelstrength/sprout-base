<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbase\migrations;

use craft\db\Migration;

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
        $settingsTable = '{{%sprout_settings}}';

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
