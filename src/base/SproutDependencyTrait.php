<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\base;

use Craft;

trait SproutDependencyTrait
{
    /**
     * Returns true if the given dependency exists in any other plugin
     *
     * i.e. Plugin::getInstance()->dependentPluginExists('sprout-base');
     *
     * @param string $module
     *
     * @return bool
     */
    public function dependencyInUse(string $module): bool
    {
        $plugins = Craft::$app->getPlugins()->getAllPlugins();

        // Filter out all plugins that don't implement SproutDependencyInterface
        // and exclude the plugin calling this method
        $dependentPlugins = array_filter($plugins, static function($plugin) {
            return $plugin instanceof SproutDependencyInterface &&
                get_class($plugin) !== get_class(self::getInstance());
        });

        foreach ($dependentPlugins as $plugin) {
            if (in_array($module, $plugin->getSproutDependencies(), true)) {
                return true;
            }
        }

        return false;
    }
}