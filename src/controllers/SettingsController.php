<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\controllers;

use barrelstrength\sproutbase\base\SproutEditionsInterface;
use barrelstrength\sproutbase\base\SproutSettingsInterface;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\errors\InvalidPluginException;
use craft\errors\MissingComponentException;
use craft\web\Controller;
use Exception;
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
    /**
     * The active Plugin class
     *
     * @var Plugin|SproutEditionsInterface
     */
    public $plugin;

    /**
     * The section of the settings area that is being edited
     *
     * <plugin-name>/settings/<settingsSection>
     *
     * @var string
     */
    public $settingsSection;

    /**
     * The selected settings tab
     *
     * @var string
     */
    public $selectedSidebarItem;

    /**
     * @throws \yii\web\ForbiddenHttpException
     */
    public function init()
    {
        // All Settings actions require an admin
        $this->requireAdmin();

        $pluginHandle = Craft::$app->getRequest()->getSegment(1);

        $this->settingsSection = Craft::$app->getRequest()->getSegment(3);
        $this->selectedSidebarItem = $this->settingsSection ?? 'general';

        $this->plugin = Craft::$app->getPlugins()->getPlugin($pluginHandle);
    }

    /**
     * Prepare plugin settings for output
     *
     * We merge multiple arrays for variables in our template:
     * - $settingsNav[$this->selectedSidebarItem]['variables']
     * - Craft::$app->getUrlManager()->getRouteParams()
     *
     * This makes sure we retain any params set in another controller on this request
     * by handing them to the settings layer as a variable. In the template,
     * they can be accessed as params.paramName. This was added to support Sprout Forms
     * Entry Statuses and Sprout Import/SEO Redirect tool
     *
     * @return Response
     * @throws InvalidPluginException
     */
    public function actionEditSettings($sproutBaseSettingsType = null): Response
    {
        if (!$this->plugin) {
            throw new InvalidPluginException($this->plugin->handle);
        }

        /** @var SproutSettingsInterface $settings */
        $settings = $this->plugin->getSettings();
        $settingsNav = $settings->getSettingsNavItems();

        if ($sproutBaseSettingsType !== null) {
            $settings = SproutBase::$app->settings->getBaseSettings($sproutBaseSettingsType);
        }

        $hasUpgradeLink = method_exists($this->plugin, 'getUpgradeUrl');
        $upgradeLink = $hasUpgradeLink ? $this->plugin->getUpgradeUrl() : null;

        $sectionVariables = $settingsNav[$this->selectedSidebarItem]['variables'] ?? [];

        return $this->renderTemplate('sprout-base/_settings/index', array_merge(
                [
                    'plugin' => $this->plugin,
                    'settings' => $settings,
                    'settingsNav' => $settingsNav ?? null,
                    'selectedSidebarItem' => $this->selectedSidebarItem,
                    'sproutBaseSettingsType' => $sproutBaseSettingsType,
                    'upgradeLink' => $upgradeLink
                ],
                $sectionVariables,
                Craft::$app->getUrlManager()->getRouteParams())
        );
    }

    /**
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     * @throws \yii\base\Exception
     */
    public function actionSaveSettings()
    {
        $this->requirePostRequest();

        // the submitted settings
        $settingsModel = null;
        $postSettings = Craft::$app->getRequest()->getBodyParam('settings');
        $sproutBaseSettingsType = $postSettings['sproutBaseSettingsType'] ?? null;

        if ($sproutBaseSettingsType !== null) {
            // Save settings when a plugin may not be installed
            $settings = SproutBase::$app->settings->saveBaseSettings($postSettings, $sproutBaseSettingsType);
        } else {
            $settings = SproutBase::$app->settings->saveSettings($this->plugin, $postSettings);
        }

        if ($settings->hasErrors()) {
            Craft::$app->getSession()->setError(Craft::t('sprout-base-settings', 'Couldn’t save settings.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'settings' => $settings
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-base-settings', 'Settings saved.'));

        return $this->redirectToPostedUrl();
    }
}
