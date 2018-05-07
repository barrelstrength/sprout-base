<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\sproutbase\helpers;

use craft\base\Plugin;

class UninstallHelper
{
    /**
     * @var $plugin Plugin
     */
    public $plugin;

    /**
     * @var $dependencyMap array
     */
    public $dependencyMap;

    /**
     * @var $pluginsToUninstall array
     */
    public $pluginsToUninstall;

    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->getDependencyMap();
        $this->buildUninstallChecklist();
    }

    public function getDependencyMap()
    {
        $this->dependencyMap = [
            'sprout-fields' => [],
            'sprout-import' => [
                'sprout-forms'
            ],
            'sprout-seo' => [
                'sprout-fields',
                'sprout-import'
            ],
        ];
    }

    /**
     * Create a list of plugins to uninstall. Because some plugins depend on
     * others that may or may not be installed, we need to make sure we don't
     * uninstall anything that another plugin may depend on.
     *
     * @todo - refactor, verbose
     *
     * @return null
     */
    public function buildUninstallChecklist()
    {
        $pluginsToUninstall[$this->plugin->id] = $this->plugin->id;

        $allDependencies = $this->dependencyMap;

        // Get all dependencies for the plugins we are uninstalling
        $pluginDependencies = $allDependencies[$this->plugin->id];

        foreach ($pluginDependencies as $dependency) {
            $pluginsToUninstall[$dependency] = $dependency;
        }

        if (!$pluginsToUninstall) {
            return null;
        }

        // Get all dependencies for the plugins we are not uninstalling
        unset($allDependencies[$this->plugin->id]);
        $remainingDependencies = $allDependencies;

        // Remove any dependencies that are needed for other plugins, we don't want to uninstall those yet
        foreach ($remainingDependencies as $plugin => $pluginDependencies) {
            if (count($pluginDependencies)) {
                foreach ($pluginDependencies as $pluginDependency) {
                    if (isset($pluginsToUninstall[$pluginDependency]) && ($pluginDependency === $pluginsToUninstall[$pluginDependency])) {
                        unset($pluginsToUninstall[$pluginDependency]);
                    }
                }
            }
        }

        $this->pluginsToUninstall = $pluginsToUninstall;
    }

    /**
     * Assists with uninstalling Sprout Plugins that no longer have any other plugin dependencies
     *
     * @todo - do we need this?
     *       Removing it caused an issue in another plugin. Investigate.
     */
    public function uninstall()
    {
//        foreach ($this->pluginsToUninstall as $plugin) {
//             Check to see if an Uninstall Class exists
//
//             Run the uninstall method of that class
//        }
    }
}