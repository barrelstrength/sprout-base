<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\elements\User;
use craft\helpers\StringHelper;
use yii\base\Component;
use yii\web\ForbiddenHttpException;


class Settings extends Component
{
    /**
     * Get a list of shared permissions
     *
     * @example
     * Via Sprout Reports
     * [
     *    'sproutReports-viewReports' => 'sproutReports-viewReports',
     *    'sproutReports-editReports' => 'sproutReports-editReports',
     * ]
     *
     * Via Sprout Forms
     * [
     *    'sproutReports-viewReports' => 'sproutForms-viewReports',
     *    'sproutReports-editReports' => 'sproutForms-editReports',
     * ]
     *
     * Once retrieved:
     * SproutBase::$app->settings->getSharedPermissions($permissionNames, 'sprout-reports', $currentPluginHandle);
     *
     * Access permissions using array syntax and the primary plugin permission name:
     * $this->requirePermission($this->permissions['sproutReports-viewReports']);
     *
     * @param array  $permissionNames
     * @param string $basePluginHandle
     * @param string $currentPluginHandle
     *
     * @return array
     */
    public function getSharedPermissions(array $permissionNames, string $basePluginHandle, string $currentPluginHandle): array
    {
        $permissions = [];

        foreach ($permissionNames as $permissionName) {
            $basePluginPermissionName = StringHelper::toCamelCase($basePluginHandle).'-'.$permissionName;
            $currentPluginPermissionName = StringHelper::toCamelCase($currentPluginHandle).'-'.$permissionName;

            $permissions[$basePluginPermissionName] = $currentPluginPermissionName;
        }

        return $permissions;
    }

    /**
     * @param $plugin Plugin
     * @param $settings
     *
     * @return Model
     */
    public function saveSettings($plugin, $settings): Model
    {
        // The existing settings
        $pluginSettings = $plugin->getSettings();

        // Have namespace?
        $settings = $settings['settings'] ?? $settings;
        // Set sprout scenario validation on the settings model
        $scenario = $settings['validationScenario'] ?? null;

        foreach ($pluginSettings->getAttributes() as $settingHandle => $value) {
            if (isset($settings[$settingHandle])) {
                $pluginSettings->{$settingHandle} = $settings[$settingHandle] ?? $value;
            }
        }

        if ($scenario) {
            $pluginSettings->setScenario($scenario);
        }

        if (!$pluginSettings->validate()) {
            return $pluginSettings;
        }

        Craft::$app->getPlugins()->savePluginSettings($plugin, $pluginSettings->getAttributes());

        return $pluginSettings;
    }
}
