<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\controllers;

use barrelstrength\sproutbase\config\base\Config as BaseConfig;
use barrelstrength\sproutbase\config\services\Config;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\errors\MissingComponentException;
use craft\errors\SiteNotFoundException;
use craft\helpers\UrlHelper;
use craft\models\Site;
use craft\web\Controller;
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

    public function actionWelcomeTemplate(string $pluginId): Response
    {
        return $this->renderTemplate('sprout/_welcome/'.$pluginId.'/welcome');
    }

    public function actionUpgradeTemplate(string $pluginId): Response
    {
        return $this->renderTemplate('sprout/_welcome/'.$pluginId.'/upgrade');
    }

    /**
     * @param string $settingsTarget
     * @param null $configKey
     * @param null $subNavKey
     *
     * @return Response
     * @throws SiteNotFoundException
     */
    public function actionEditSettings(
        $settingsTarget = self::SETTINGS_TARGET_PROJECT_CONFIG,
        $configKey = null,
        $subNavKey = null
    ): Response {

        $siteHandle = Craft::$app->getRequest()->getQueryParam('site');

        $primarySite = Craft::$app->getSites()->getPrimarySite();
        $currentSite = $siteHandle !== null
            ? Craft::$app->getSites()->getSiteByHandle($siteHandle)
            : $primarySite;

        $sproutConfigs = SproutBase::$app->config->getConfigs(false);

        /** @var BaseConfig $currentSproutConfig */
        $currentSproutConfig = $sproutConfigs[$configKey];

        $settings = SproutBase::$app->settings->getSettings(false);

        $currentSettings = $settings[$currentSproutConfig->getKey()] ?? [];

        // CP settings go first
        $cpSettings['control-panel'] = $settings['control-panel'];
        unset($settings['control-panel']);
        $settings = array_merge($cpSettings, $settings);

        $subNav = $this->buildSubNav($sproutConfigs, $settings, $currentSite);

        // We grab the config settings a second time for configWarning messages
        $fileConfig = Craft::$app->getConfig()->getConfigFromFile('sprout');
        $currentFileConfig = $fileConfig[$configKey] ?? [];

        $navItem = $currentSettings->getSettingsNavItem();
        $defaultSubNavKey = key($navItem);
        $currentSubNavKey = $subNavKey ?? $defaultSubNavKey;

        $subSection = $navItem[$currentSubNavKey];
        $dynamicVariables = $subSection['variables'] ?? [];

        // Throw error if not found?
        $currentSection = $subNav[$currentSubNavKey];

        if (isset($currentSection['settingsTarget'])) {
            $settingsTarget = $currentSection['settingsTarget'];
        }

        // The settingsTarget defaults to 'project-config'
        // Plugins should pass a settingsTarget of 'db' if they
        // wish to manage their settings on their own
        $settingsTemplate = $settingsTarget === 'db'
            ? 'sprout/_layouts/settings-wrapper'
            : 'sprout/_layouts/settings';

        $showMultiSiteSettings = $currentSection['multisite'] ?? false;
        $packAssociativeArrays = $currentSection['packAssociativeArrays'] ?? false;

        return $this->renderTemplate($settingsTemplate, array_merge([
            'currentSite' => $currentSite,
            'sproutConfig' => $currentSproutConfig,
            'settings' => $currentSettings,
            'config' => $currentFileConfig,
            'subnav' => $subNav,
            'currentSection' => $currentSection,
            'configKey' => $configKey,
            'currentSubNavKey' => $currentSubNavKey,
            'showMultiSiteSettings' => $showMultiSiteSettings,
            'packAssociativeArrays' => $packAssociativeArrays
        ], $dynamicVariables));
    }

    /**
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     * @throws Exception
     * @throws ErrorException
     */
    public function actionSaveSettings()
    {
        $this->requirePostRequest();

        $settingsSection = Craft::$app->getRequest()->getBodyParam('settingsSection');
        $packAssociativeArrays = Craft::$app->getRequest()->getBodyParam('packAssociativeArrays');
        $postSettings = Craft::$app->getRequest()->getBodyParam('settings');

        $config = SproutBase::$app->config->getConfigByKey($settingsSection, false);
        $projectConfigSettingsKey = Config::CONFIG_SPROUT_KEY.'.'.$config->getKey();

        $settings = SproutBase::$app->settings->getSettingsByKey($settingsSection, false);
        $settings->setAttributes($postSettings, false);

        if (!SproutBase::$app->settings->saveSettings($projectConfigSettingsKey, $settings, $packAssociativeArrays)) {
            Craft::$app->getSession()->setError(Craft::t('sprout', 'Couldn’t save settings.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'settings' => $settings
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Settings saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * @param array $settings
     * @param Site $currentSite
     * @param array $sproutConfigs
     *
     * @return array
     */
    protected function buildSubNav(array $sproutConfigs, array $settings, Site $currentSite = null): array
    {

        $subNavGroups = [];

        // Loop through once to establish our groupings
        foreach ($settings as $settingsKey => $setting) {

            $setting->setCurrentSite($currentSite);

            $settingsNavItem = $setting->getSettingsNavItem();
            $settingsSubNavItems = $settingsNavItem ?? [];

            if (!$settingsSubNavItems) {
                continue;
            }

            /** @var BaseConfig $matchingSproutConfig */
            $matchingSproutConfig = $sproutConfigs[$settingsKey];

            if ($matchingSproutConfig->getKey() !== 'control-panel' &&
                !$setting->getIsEnabled()) {
                continue;
            }

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
