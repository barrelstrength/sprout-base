<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\migrations\install;

use barrelstrength\sproutbase\config\base\SproutBasePlugin;
use barrelstrength\sproutbase\config\services\Config;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class BaseInstall extends Migration
{
    private $plugin;

    public function __construct(SproutBasePlugin $plugin, $config = [])
    {

        $this->plugin = $plugin;

        parent::__construct($config);
    }

    /**
     * @throws ErrorException
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function safeUp()
    {
        $sproutConfigTypes = SproutBase::SPROUT_MODULES;

        foreach ($sproutConfigTypes as $sproutConfigType) {
            $sproutConfig = new $sproutConfigType();
            $this->runInstallMigration($sproutConfig);

            $subModuleConfigTypes = $sproutConfigType::getSproutConfigDependencies();

            foreach ($subModuleConfigTypes as $subModuleConfigType) {
                $subModuleConfig = new $subModuleConfigType();
                $this->runInstallMigration($subModuleConfig);
            }
        }
    }

    /**
     * @param $sproutConfig
     *
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    public function runInstallMigration($sproutConfig)
    {
        $cpSettings = SproutBase::$app->config->getCpSettings();
        $cpSettingsProjectConfigKey = Config::CONFIG_SPROUT_KEY.'.control-panel';

        // Run the safeUp method if our module has an Install migration
        if ($migration = $sproutConfig->createInstallMigration()) {
            ob_start();
            $migration->safeUp();
            ob_end_clean();

            $projectConfigSettingsKey = Config::CONFIG_SPROUT_KEY.'.'.$sproutConfig::getKey();
            $settings = $sproutConfig->createSettingsModel();

            if ($settings) {
                $settings->beforeAddDefaultSettings();
                SproutBase::$app->settings->saveSettings($projectConfigSettingsKey, $settings);
            }

            $cpSettings->modules[$sproutConfig->getKey()] = [
                'alternateName' => '',
                'enabled' => 1,
                'moduleKey' => $sproutConfig->getKey(),
            ];

            SproutBase::$app->settings->saveSettings($cpSettingsProjectConfigKey, $cpSettings);
        }
    }

    public function safeDown()
    {
        $sproutPluginIds = SproutBase::SPROUT_PLUGIN_IDS;

        $plugins = (new Query())
            ->select('handle')
            ->from(Table::PLUGINS)
            ->where(['in', 'handle', $sproutPluginIds])
            ->column();

        // If we have more than one sprout base-driven plugin, don't uninstall
        if ($plugins !== null && count($plugins) > 1) {
            return;
        }

        // if we have only one plugin, confirm that it's the one we think it is
        if (array_shift($plugins) !== $this->plugin->id) {
            // Don't blow up, but the db likely needs to be reviewed
            return;
        }

        // If we're currently uninstalling the final
        // Sprout Base plugin: Uninstall Everything
        $sproutProjectConfigKey = Config::CONFIG_SPROUT_KEY;
        $sproutConfigTypes = SproutBase::SPROUT_MODULES;

        foreach ($sproutConfigTypes as $sproutConfigType) {
            $sproutConfig = new $sproutConfigType();

            // Run the safeDown method if our module has an Install migration
            if ($migration = $sproutConfig->createInstallMigration()) {
                ob_start();
                $migration->safeDown();
                ob_end_clean();
            }
        }

        // Remove all sprout settings from project config
        Craft::$app->getProjectConfig()->remove($sproutProjectConfigKey);
    }
}
