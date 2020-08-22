<?php

namespace barrelstrength\sproutbase\migrations;

use barrelstrength\sproutbase\config\base\Config;
use Craft;
use craft\db\Migration;
use craft\errors\InvalidPluginException;
use Throwable;

class m200700_000000_add_reports_editions extends Migration
{
    /**
     * @return bool
     * @throws Throwable
     * @throws InvalidPluginException
     */
    public function safeUp(): bool
    {
        // Don't make the same config changes twice
        $projectConfig = Craft::$app->getProjectConfig();
        $schemaVersion = $projectConfig->get('plugins.sprout-reports.schemaVersion', true);
        if (version_compare($schemaVersion, '2.0.0', '>=')) {
            return true;
        }

        Craft::$app->getPlugins()->switchEdition('sprout-reports', Config::EDITION_PRO);
    }

    public function safeDown(): bool
    {
        echo "m200700_000000_add_reports_editions cannot be reverted.\n";

        return false;
    }
}
