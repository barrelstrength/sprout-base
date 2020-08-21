<?php

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbase\migrations;

use craft\db\Migration;

class m200701_000000_update_base_schema extends Migration
{
    public function safeUp()
    {
        $migrations = [
            'barrelstrength\sproutbase\migrations\install\CampaignsInstall',
            'barrelstrength\sproutbase\migrations\install\EmailInstall',
            'barrelstrength\sproutbase\migrations\install\FieldsInstall',
            'barrelstrength\sproutbase\migrations\install\FormsInstall',
            'barrelstrength\sproutbase\migrations\install\ListsInstall',
            'barrelstrength\sproutbase\migrations\install\MetadataInstall',
            'barrelstrength\sproutbase\migrations\install\RedirectsInstall',
            'barrelstrength\sproutbase\migrations\install\ReportsInstall',
            'barrelstrength\sproutbase\migrations\install\SentEmailInstall',
            'barrelstrength\sproutbase\migrations\install\SitemapsInstall',
        ];

        // Install a fresh version of our base tables with updated naming conventions
        // and we'll migrate the existing data to these new tables
        foreach ($migrations as $migrationClass) {
            if ($migration = new $migrationClass()) {
                ob_start();
                $migration->safeUp();
                ob_end_clean();
            }
        }
    }

    public function safeDown(): bool
    {
        echo "m200701_000000_update_base_schema cannot be reverted.\n";

        return false;
    }
}
