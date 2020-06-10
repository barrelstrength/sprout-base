<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\controllers;

use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\base\Settings;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\errors\MissingComponentException;
use craft\errors\SiteNotFoundException;
use craft\helpers\UrlHelper;
use craft\models\Site;
use craft\web\Controller;
use ReflectionException;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\Response;

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
        return $this->redirect(UrlHelper::cpUrl('sprout/settings/control-panel/welcome'));
    }

    /**
     * @param string $settingsTarget
     * @param null   $siteHandle
     * @param null   $settingsSectionHandle
     * @param null   $settingsSubSectionHandle
     *
     * @return Response
     * @throws SiteNotFoundException
     * @throws ReflectionException
     */
    public function actionEditSettings(
        $settingsTarget = self::SETTINGS_TARGET_PROJECT_CONFIG,
        $settingsSectionHandle = null,
        $settingsSubSectionHandle = null
    ): Response {
//        $hasUpgradeLink = method_exists($this->plugin, 'getUpgradeUrl');
//        $upgradeLink = $hasUpgradeLink ? $this->plugin->getUpgradeUrl() : null;

        $siteHandle = Craft::$app->getRequest()->getQueryParam('site');

        $currentSite = $siteHandle !== null
            ? Craft::$app->getSites()->getSiteByHandle($siteHandle)
            : Craft::$app->getSites()->getPrimarySite();

        $sproutConfigs = SproutBase::$app->config->getConfigs();

        /** @var Config $currentSproutConfig */
        $currentSproutConfig = $sproutConfigs[$settingsSectionHandle];

        $settings = SproutBase::$app->settings->getSettings(false);
        $currentSettings = $settings[$currentSproutConfig->getKey()] ?? [];

        // CP settings go first
        $cpSettings['control-panel'] = $settings['control-panel'];
        unset($settings['control-panel']);
        $settings = array_merge($cpSettings, $settings);

        $subNav = $this->buildSubNav($sproutConfigs, $settings, $currentSite);

        // We grab the config settings a second time for configWarning messages
        $fileConfig = Craft::$app->getConfig()->getConfigFromFile('sprout');
        $currentFileConfig = $fileConfig[$settingsSectionHandle] ?? [];

        $navItem = $currentSettings->getSettingsNavItem();
        $defaultSubSectionHandle = key($navItem);
        $currentSubSectionHandle = $settingsSubSectionHandle ?? $defaultSubSectionHandle;

        $subSection = $navItem[$currentSubSectionHandle];
        $dynamicVariables = $subSection['variables'] ?? [];

        // Throw error if not found?
        $currentSubsection = $subNav[$currentSubSectionHandle];

        if (isset($currentSubsection['settingsTarget'])) {
            $settingsTarget = $currentSubsection['settingsTarget'];
        }

        // The settingsTarget defaults to 'project-config'
        // Plugins should pass a settingsTarget of 'db' if they
        // wish to manage their settings on their own
        $settingsTemplate = $settingsTarget === 'db'
            ? 'sprout/config/_layouts/settings-wrapper'
            : 'sprout/config/_layouts/settings';

        $showMultiSiteSettings = $currentSubsection['multisite'] ?? false;

        return $this->renderTemplate($settingsTemplate, array_merge([
            'currentSite' => $currentSite,
            'settings' => $currentSettings,
            'config' => $currentFileConfig,
            'subnav' => $subNav,
            'currentSubsection' => $currentSubsection,

            'settingsSectionHandle' => $settingsSectionHandle,
            'currentSubSectionHandle' => $currentSubSectionHandle,
            'showMultiSiteSettings' => $showMultiSiteSettings,

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
        $siteId = Craft::$app->getRequest()->getBodyParam('siteId');
        $postSettings = Craft::$app->getRequest()->getBodyParam('settings');

        $currentSite = $siteId !== null
            ? Craft::$app->getSites()->getSiteById($siteId)
            : Craft::$app->getSites()->getPrimarySite();

        /** @var Settings $settingsModel */
        $settingsModel = SproutBase::$app->settings->getSettingsByKey($settingsSection, false);
        $settingsModel->setAttributes($postSettings, false);

        if (!SproutBase::$app->settings->saveSettings($settingsModel, $currentSite)) {
            Craft::$app->getSession()->setError(Craft::t('sprout', 'Couldn’t save settings.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'settings' => $settingsModel
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Settings saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * @param array $settings
     * @param Site  $currentSite
     * @param array $sproutConfigs
     *
     * @return array
     */
    protected function buildSubNav(
        array $sproutConfigs,
        array $settings,
        Site $currentSite = null
    ): array {
        $subNavGroups = [];

        // Loop through once to establish our groupings
        foreach ($settings as $settingsKey => $setting) {

            $setting->setCurrentSite($currentSite);

            $settingsNavItem = $setting->getSettingsNavItem();
            $settingsSubNavItems = $settingsNavItem ?? [];

            if (!$settingsSubNavItems) {
                continue;
            }

            /** @var Config $matchingSproutConfig */
            $matchingSproutConfig = $sproutConfigs[$settingsKey];
            $configGroup = $matchingSproutConfig->getConfigGroup();

            $heading = $configGroup !== null
                ? $configGroup::groupName()
                : $matchingSproutConfig::groupName();

            foreach ($settingsSubNavItems as $subNavKey => $settingsSubNavItem) {
                $settingsSubNavItem['url'] = 'sprout/settings/'.$settingsKey.'/'.$subNavKey;

                $subNavGroups[$heading][$subNavKey] = $settingsSubNavItem;
            }
        }

        $subNav = [];

        // Loop through again to use our groupings and build our nav
        foreach ($subNavGroups as $heading => $subNavGroup) {
            $subNav[] = [
                'heading' => $heading
            ];

            ksort($subNavGroup);

            foreach ($subNavGroup as $navKey => $navItem) {
                $subNav[$navKey] = $navItem;
            }
        }

        return $subNav;
    }
}
