<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services;

use barrelstrength\sproutbase\base\SharedPermissionsInterface;
use barrelstrength\sproutbase\base\SproutSettingsInterface;
use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\db\Query;
use craft\helpers\StringHelper;
use craft\services\Plugins;
use yii\base\Component;

class Settings extends Component
{
    /**
     * Due to the module-based architecture of the Sprout plugin suite in many cases
     * modules handle checks for settings and permissions that may exist in multiple
     * plugins. This method helps determine the specific plugin making the request.
     *
     * Web requests are supported by default where possible. In some cases,
     * such as via ajax requests and console commands, an action may have to
     * specifically identify which plugin it is coming from.
     *
     * 1. Check if the pluginHandle is provided
     * 2. Check the POST request for a pluginHandle attribute
     * 3. Try to grab the plugin-handle from the first segment of the URL
     *
     * @param string|null $pluginHandle
     *
     * @return mixed|string|null
     */
    public function getPluginHandle(string $pluginHandle = null) {

        if ($pluginHandle !== null) {
            return $pluginHandle;
        }

        return Craft::$app->request->getParam('pluginHandle') ?? Craft::$app->request->getSegment(1);
    }

    /**
     * @param string|array $pluginHandle
     *
     * @return Model|null
     */
    public function getPluginSettings(string $pluginHandle = null)
    {
        $currentPluginHandle = $this->getPluginHandle($pluginHandle);

        if ($plugin = Craft::$app->getPlugins()->getPlugin($currentPluginHandle)) {
            return $plugin->getSettings();
        }

        return null;
    }

    /**
     * Get a list of shared permissions and determine which plugin we should be checking permissions for.
     * Because we have a module-based architecture often the classes determining permissions are outside
     * of a given plugin or shared by multiple plugins. This method helps resolve all that.
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
     * To use:
     * use barrelstrength\sproutbase\services\Settings as SproutBaseSettingsService;
     * $this->permissions = SproutBase::$app->settings->getSharedPermissions(new Settings(), 'sprout-reports');
     *
     * Access permissions using array syntax and the primary plugin permission name:
     * $this->requirePermission($this->permissions['sproutReports-viewReports']);
     *
     * @param SharedPermissionsInterface $settings
     * @param string                     $basePluginHandle
     * @param string                     $pluginHandle
     *
     * @return array
     */
    public function getPluginPermissions(SharedPermissionsInterface $settings, string $basePluginHandle, string $pluginHandle = null): array
    {
        $currentPluginHandle = $this->getPluginHandle($pluginHandle);
        $permissionNames = $settings->getSharedPermissions();
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

    /**
     * Save plugin settings shared between two or more Sprout Plugins
     *
     * @param $pluginHandle
     * @param string $settingsModel
     * @param $postSettings
     * @return Model
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     * @throws \yii\base\NotSupportedException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function saveSettingsViaProjectConfig($pluginHandle, string $settingsModel, $postSettings): Model
    {
        // The existing settings
        $projectConfig = Craft::$app->getProjectConfig();
        $sproutSettings = $projectConfig->get('plugins.'.$pluginHandle.'.settings');
        /** @var Model $settings */
        $settings = new $settingsModel;
        $settings->setAttributes($sproutSettings, false);
        $settings->setAttributes($postSettings, false);

        // Set sprout scenario validation on the settings model
        $scenario = $settings['validationScenario'] ?? null;

        if ($scenario) {
            $settings->setScenario($scenario);
        }

        if (!$settings->validate()) {
            return $settings;
        }

        $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings', $settings->toArray());

        return $settings;
    }

    /**
     * @param string $settingsModel
     * @return Model
     */
    public function getBaseSettings(string $settingsModel): Model
    {
        $query = $this->getBaseSettingsQuery($settingsModel);

        $settingsJson = $query['settings'] ?? null;

        $settings = new  $settingsModel();

        if ($settingsJson){
            $settingsArray = json_decode($settingsJson, true);
            $settings->setAttributes($settingsArray, false);
        }

        return $settings;
    }

    /**
     * @param array $settingsArray
     * @param $settingsModel
     * @return mixed
     * @throws \yii\db\Exception
     */
    public function saveBaseSettings(array $settingsArray, $settingsModel)
    {
        $settings = $this->getBaseSettings($settingsModel);
        $settings->setAttributes($settingsArray, false);
        $settingsAsJson = json_encode($settings->toArray());

        Craft::$app->db->createCommand()->update('{{%sprout_settings}}',
            ['settings' => $settingsAsJson],
            ['model' => $settingsModel]
        )->execute();

        return $settings;
    }

    /**
     * @param $settingsModel
     * @return array|bool
     */
    private function getBaseSettingsQuery($settingsModel)
    {
        $query = (new Query())
            ->select(['settings'])
            ->from(['{{%sprout_settings}}'])
            ->where(['model' => $settingsModel])
            ->one();

        return $query;
    }
}
