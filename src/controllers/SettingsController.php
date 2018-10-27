<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\controllers;

use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Plugin;
use craft\errors\InvalidPluginException;
use craft\web\Controller as BaseController;
use yii\web\BadRequestHttpException;

/**
 * Manage plugin settings from a custom plugin settings area on the Plugin tab
 *
 * Using the Sprout Base settings controller requires:
 *
 * 1. Adding two routes to a plugin:
 * 'sprout-seo/settings' => 'sprout-base/settings/edit-settings',
 * 'sprout-seo/settings/<settingsSectionHandle:.*>' => 'sprout-base/settings/edit-settings'
 *
 * 2. Submitting your settings form to Sprout Base
 * <input type="hidden" name="action" value="sprout-base/settings/save-settings">
 *
 * 3. Ensuring all settings are included in a settings array of the submitted form
 * <input type="text" name="settings[pluginNameOverride]" value="">
 *
 * 4. Defining all settings in the pluginname/models/Settings.php file
 */
class SettingsController extends BaseController
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
     * @inheritdoc
     */
    public function init()
    {
        $pluginHandle = Craft::$app->getRequest()->getSegment(1);

        $this->settingsSection = Craft::$app->getRequest()->getSegment(3);
        $this->selectedSidebarItem = $this->settingsSection ?? 'general';

        $this->plugin = Craft::$app->getPlugins()->getPlugin($pluginHandle);
    }

    /**
     * Prepare plugin settings for output
     *
     * @return \yii\web\Response
     * @throws InvalidPluginException
     */
    public function actionEditSettings()
    {
        if (!$this->plugin) {
            throw new InvalidPluginException($this->plugin->handle);
        }

        // @todo - is there a better way to do this?
        // This was added to support the Sprout Import, SEO Redirect tool
        //
        // Make sure we retain any params set in another controller on this request
        // by handing them to the settings layer as a variable. In the template,
        // they can be accessed as params.paramName
        $settingsNav = $this->plugin->getSettings()->getSettingsNavItems();
        $variables = $settingsNav[$this->selectedSidebarItem]['variables'] ?? [];

        $variables['plugin'] = $this->plugin;
        $variables['selectedSidebarItem'] = $this->selectedSidebarItem;

        $variables = array_merge($variables, Craft::$app->getUrlManager()->getRouteParams());

        return $this->renderTemplate('sprout-base/_settings/index', $variables);
    }

    /**
     * Saves plugin settings
     *
     * @return null|\yii\web\Response
     * @throws BadRequestHttpException
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\db\Exception
     */
    public function actionSaveSettings()
    {
        $this->requirePostRequest();

        // the submitted settings
        $postSettings = Craft::$app->getRequest()->getBodyParam('settings');
        $settings = SproutBase::$app->settings->saveSettings($this->plugin, $postSettings);

        if ($settings->hasErrors()) {
            Craft::$app->getSession()->setError(Craft::t('sprout-base', 'Couldn’t save settings.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'settings' => $settings
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-base', 'Settings saved.'));

        return $this->redirectToPostedUrl();
    }
}
