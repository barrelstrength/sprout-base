<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\base;

use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Plugin;
use craft\db\Migration;
use craft\db\MigrationManager;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\FileHelper;
use craft\web\UrlManager;
use yii\base\Event;
use yii\base\InvalidConfigException;
use barrelstrength\sproutbase\migrations\install\Install as SproutBaseInstall;

abstract class SproutBasePlugin extends Plugin
{
    /**
     * Events that need to call SproutBasePlugin::getSproutConfigs()
     * before all plugins are loaded should be added here
     */
    public function init()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getCpUrlRules());
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getSiteUrlRules());
        });
    }

    /**
     * @return MigrationManager
     * @throws InvalidConfigException
     */
    public function getMigrator(): MigrationManager
    {
        /** @var MigrationManager $migrationManager */
        $migrationManager = Craft::createObject([
            'class' => MigrationManager::class,
            'track' => SproutBase::MODULE_ID,
            'migrationNamespace' => SproutBase::MIGRATION_NAMESPACE,
            'migrationPath' => SproutBase::MIGRATION_PATH,
        ]);

        return $migrationManager;
    }

    /**
     * Plugins will manage their own Install migration which will
     * trigger a check for all relevant module migrations
     *
     * @return Migration|mixed|null
     */
    protected function createInstallMigration()
    {
        $alias = '@vendor/barrelstrength/sprout-base/src/migrations/install/Install.php';
        $path = FileHelper::normalizePath(Craft::getAlias($alias));

        require_once $path;

        return new SproutBaseInstall($this);
    }

    /**
     * Implement this method on all SproutBasePlugin's so we can easily sort out all
     * Sprout plugins from the list of all installed plugins and manage dependencies
     *
     * @return ConfigInterface[]
     */
    public static function getSproutConfigs(): array
    {
        return [];
    }

    private function getCpUrlRules(): array
    {
        return $this->prepareUrlRules(static::getSproutConfigs(), 'cp');
    }

    private function getSiteUrlRules(): array
    {
        return $this->prepareUrlRules(static::getSproutConfigs(), 'site');
    }

    /**
     * Process the configs here, because we can't call getConfigs() yet
     * The plugins are still loading.
     *
     * @param array $configTypes
     * @param string $mode
     *
     * @return array
     */
    private function prepareUrlRules(array $configTypes, string $mode): array
    {
        $urlRules = [];
        foreach ($configTypes as $configType) {
            $config = new $configType();
            $subModuleConfigTypes = $configType::getSproutConfigDependencies();

            $rules = $mode === 'cp'
                ? $config->getCpUrlRules()
                : $config->getSiteUrlRules();
            foreach ($rules as $route => $details) {
                $urlRules[$route] = $details;
            }

            foreach ($subModuleConfigTypes as $subModuleConfigType) {
                $subModuleConfig = new $subModuleConfigType();
                $rules = $mode === 'cp'
                    ? $subModuleConfig->getCpUrlRules()
                    : $config->getSiteUrlRules();
                foreach ($rules as $route => $details) {
                    $urlRules[$route] = $details;
                }
            }
        }

        return $urlRules;
    }
}

