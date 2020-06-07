<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\base;

use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;

abstract class SproutCentralPlugin extends Plugin
{
    /**
     * Events that need to call SproutCentralPlugin::getSproutConfigs()
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
     * Implement this interface on all plugins so
     * we can easily sort out all Sprout plugins
     * from the list of all installed plugins and
     * manage dependencies
     *
     * @return ConfigInterface[]
     */
    public function getSproutConfigs(): array
    {
        return [];
    }

    private function getCpUrlRules(): array
    {
        $configTypes = $this->getSproutConfigs();

        $urlRules = [];
        foreach ($configTypes as $configType) {
            $config = new $configType();
            $rules = $config->getCpUrlRules();
            foreach ($rules as $route => $details) {
                $urlRules[$route] = $details;
            }
        }

        return $urlRules;
    }

    private function getSiteUrlRules(): array
    {
        $configTypes = $this->getSproutConfigs();

        $urlRules = [];
        foreach ($configTypes as $configType) {
            $config = new $configType();
            $rules = $config->getSiteUrlRules();
            foreach ($rules as $route => $details) {
                $urlRules[$route] = $details;
            }
        }

        return $urlRules;
    }
}

