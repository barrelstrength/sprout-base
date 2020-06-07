<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\controllers;

use barrelstrength\sproutbase\config\base\Settings;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\errors\MissingComponentException;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use ReflectionException;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Manage plugin settings from a custom plugin settings area on the Plugin tab
 *
 * Using the Sprout Base settings controller requires:
 *
 * 1. Adding two routes to a plugin:
 * 'sprout-seo/settings' => 'sprout/settings/edit-settings',
 * 'sprout-seo/settings/<settingsSectionHandle:.*>' => 'sprout/settings/edit-settings'
 *
 * 2. Submitting your settings form to Sprout Base
 * <input type="hidden" name="action" value="sprout/settings/save-settings">
 *
 * 3. Ensuring all settings are included in a settings array of the submitted form
 * <input type="text" name="settings[pluginNameOverride]" value="">
 *
 * 4. Defining all settings in the pluginname/models/Settings.php file
 */
class SettingsController extends Controller
{
    const SETTINGS_TARGET_PROJECT_CONFIG = 'project-config';
    const SETTINGS_TARGET_DB = 'db';

    /**
     * Send user to the Sprout hello page if they are messing
     * with the URL and we don't know what to do
     *
     * @return Response
     */
    public function actionHello(): Response
    {
        return $this->redirect(UrlHelper::cpUrl('sprout/settings/general'));
    }

    /**
     * @param string $settingsTarget
     * @param null   $settingsSectionHandle
     * @param null   $settingsSubSectionHandle
     *
     * @return Response
     * @throws ReflectionException
     */
    public function actionEditSettings(
        $settingsTarget = self::SETTINGS_TARGET_PROJECT_CONFIG,
        $settingsSectionHandle = null,
        $settingsSubSectionHandle = null
    ): Response {
//        $hasUpgradeLink = method_exists($this->plugin, 'getUpgradeUrl');
//        $upgradeLink = $hasUpgradeLink ? $this->plugin->getUpgradeUrl() : null;

        $settings = SproutBase::$app->settings->getSettings(false);

        // Place general settings at end
        $generalSettings['general'] = $settings['general'];
        unset($settings['general']);
        $settings = array_merge($generalSettings, $settings);

        $subNav = [];
        foreach ($settings as $setting) {

            $settingsNavItem = $setting->getSettingsNavItem();
            $settingsSubNavItems = $settingsNavItem['subnav'] ?? [];

            if (!$settingsSubNavItems) {
                continue;
            }

            $subNav[] = [
                'heading' => $settingsNavItem['label'],
            ];

            foreach ($settingsSubNavItems as $subNavKey => $settingsSubNavItem) {
                $subNav[$subNavKey] = $settingsSubNavItem;
            }
        }


        $currentSettings = $settings[$settingsSectionHandle] ?? [];

        // We grab the config settings a second time for configWarning messages
        $config = Craft::$app->getConfig()->getConfigFromFile('sprout');
        $currentConfig = $config[$settingsSectionHandle] ?? [];

        $navItem = $currentSettings->getSettingsNavItem();
        $defaultSubSectionHandle = key($navItem['subnav']);
        $currentSubSectionHandle = $settingsSubSectionHandle ?? $defaultSubSectionHandle;

        $subSection = $navItem['subnav'][$currentSubSectionHandle];
        $dynamicVariables = $subSection['variables'] ?? [];

        // Throw error if not found?
        $currentSubsection = $subNav[$currentSubSectionHandle];

        // The settingsTarget defaults to 'project-config'
        // Plugins should pass a settingsTarget of 'db' if they
        // wish to manage their settings on their own
        $settingsTemplate = $settingsTarget === 'db'
            ? 'sprout-base-config/_layouts/settings-wrapper'
            : 'sprout-base-config/_layouts/settings';

        return $this->renderTemplate($settingsTemplate, array_merge([
            'settings' => $currentSettings,
            'config' => $currentConfig,
//            'navItem' => $navItem ?? null,

            'subnav' => $subNav,
            'currentSubsection' => $currentSubsection,

            'settingsSectionHandle' => $settingsSectionHandle,
            'currentSubSectionHandle' => $currentSubSectionHandle,
//            'upgradeLink' => $upgradeLink
        ], $dynamicVariables));
    }

    /**
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     * @throws Exception
     * @throws ReflectionException
     * @throws ErrorException
     */
    public function actionSaveSettings()
    {
        $this->requirePostRequest();

        // the submitted settings
        $settingsModel = null;
        $settingsSection = Craft::$app->getRequest()->getBodyParam('settingsSection');
        $postSettings = Craft::$app->getRequest()->getBodyParam('settings');

        /** @var Settings $settingsModel */
        $settingsModel = SproutBase::$app->settings->getSettingsByKey($settingsSection, false);
        $settingsModel->setAttributes($postSettings, false);

        if (!SproutBase::$app->settings->saveSettings($settingsModel)) {
            Craft::$app->getSession()->setError(Craft::t('sprout', 'Couldn’t save settings.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'settings' => $settingsModel
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Settings saved.'));

        return $this->redirectToPostedUrl();
    }

}
