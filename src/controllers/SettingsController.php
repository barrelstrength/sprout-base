<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\controllers;

use barrelstrength\sproutbase\base\SproutSettingsInterface;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\errors\InvalidPluginException;
use craft\web\Controller;
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
     * @var Plugin
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
        $variables['sproutBaseSettingsType'] = $sproutBaseSettingsType;

        if (!is_null($sproutBaseSettingsType)){
            $settings = SproutBase::$app->settings->getBaseSettings($sproutBaseSettingsType);
        }

        // @todo - is there a better way to do this?
        // This was added to support the Sprout Import, SEO Redirect tool
        //
        // Make sure we retain any params set in another controller on this request
        // by handing them to the settings layer as a variable. In the template,
        // they can be accessed as params.paramName
        $variables = $settingsNav[$this->selectedSidebarItem]['variables'] ?? [];

        $variables['plugin'] = $this->plugin;
        $variables['selectedSidebarItem'] = $this->selectedSidebarItem;

        $variables = array_merge($variables, Craft::$app->getUrlManager()->getRouteParams());

        $variables['settings'] = $settings;
        $variables['settingsNav'] = $settingsNav ?? null;

        return $this->renderTemplate('sprout-base/_settings/index', $variables);
    }

    /**
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     * @throws \yii\base\NotSupportedException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionSaveSettings()
    {
        $this->requirePostRequest();

        // the submitted settings
        $settingsModel = null;
        $postSettings = Craft::$app->getRequest()->getBodyParam('settings');
        $sproutBaseSettingsType = $postSettings['sproutBaseSettingsType'] ?? null;

        if (!is_null($sproutBaseSettingsType)){
            // Save settings when a plugin may not be installed
            $settings = SproutBase::$app->settings->saveBaseSettings($postSettings, $sproutBaseSettingsType);
        }else{
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
