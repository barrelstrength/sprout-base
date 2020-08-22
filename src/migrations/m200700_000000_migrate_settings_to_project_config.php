<?php

namespace barrelstrength\sproutbase\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class m200700_000000_migrate_settings_to_project_config extends Migration
{
    /**
     * @return bool
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    public function safeUp(): bool
    {
        $oldSettings = (new Query())
            ->select([
                'model',
                'settings',
            ])
            ->from(['{{%sprout_settings}}'])
            ->all();

        $settingsMap = [
            'barrelstrength\sproutbasereports\models\Settings' => 'reports',
            'barrelstrength\sproutbasesentemail\models\Settings' => 'sent-email',
            'barrelstrength\sproutbaseemail\models\Settings' => 'email',

            // @todo - Review siteSettings and groupSettings formatting
            'barrelstrength\sproutbasesitemaps\models\Settings' => 'sitemaps',
            'barrelstrength\sproutbaseredirects\models\Settings' => 'redirects',
        ];

        // Disable "Show Upgrade Messages" by default for folks when upgrading.
        // so Sprout Forms users don't all of a sudden see a bunch of upgrade stuff
        $cpSettings = [
            'enableUpgradeMessages' => false,
        ];

        foreach ($oldSettings as $settingsGroup) {

            if (!isset($settingsMap[$settingsGroup['model']])) {
                // Log NotFound
                continue;
            }

            $moduleKey = $settingsMap[$settingsGroup['model']];
            $configKey = 'plugins.sprout.'.$moduleKey;
            $newSettings = $settingsMap[$settingsGroup['oldSettings']];

            // Build the CP modules settings
            $cpSettings['modules'][] = [
                'alternateName' => $newSettings['pluginNameOverride'],
                'enabled' => 1,
                'moduleKey' => $moduleKey,
            ];

            unset($newSettings['pluginNameOverride']);

            // @todo - do we need to pack any of the settings?
            // ProjectConfigHelper::packAssociativeArrays($siteSettings)

            Craft::$app->getProjectConfig()->set($configKey, $newSettings, "Update Sprout Settings for “{$configKey}”");
        }

        Craft::$app->getProjectConfig()->set('plugins.sprout.control-panel', $cpSettings, 'Update Sprout Settings for “plugins.sprout.control-panel”');

        // delete sprout_settings table
        $this->dropTableIfExists('{{%sprout_settings}}');
    }

    public function safeDown(): bool
    {
        echo "m200700_000000_migrate_settings_to_project_config cannot be reverted.\n";

        return false;
    }
}
