<?php

namespace barrelstrength\sproutbase\migrations;

use barrelstrength\sproutbase\records\Settings as SproutBaseSettingsRecord;
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
        if (!$this->db->tableExists(SproutBaseSettingsRecord::tableName())) {
            $this->createTable(SproutBaseSettingsRecord::tableName(), [
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
        $this->dropTableIfExists(SproutBaseSettingsRecord::tableName());

        return true;
    }
}
