<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\controllers;

use barrelstrength\sproutcore\SproutCore;
use Craft;
use craft\base\Plugin;
use craft\errors\InvalidPluginException;
use craft\web\Controller as BaseController;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;

/**
 * Manage plugin settings from a custom plugin settings area on the Plugin tab
 *
 * Using the Sprout Core settings controller requires:
 *
 * 1. Adding two routes to a plugin:
 * 'sprout-seo/settings' => 'sprout-core/settings/edit-settings',
 * 'sprout-seo/settings/<settingsSectionHandle:.*>' => 'sprout-core/settings/edit-settings'
 *
 * 2. Submitting your settings form to Sprout Core
 * <input type="hidden" name="action" value="sprout-core/settings/save-settings">
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
	 * @throws InvalidParamException
	 */
	public function actionEditSettings()
	{
		if (!$this->plugin)
		{
			throw new InvalidPluginException($this->plugin->handle);
		}

		// @todo - is there a better way to do this?
		// This was added to support the Sprout Import, SEO Redirect tool
		//
		// Make sure we retain any params set in another controller on this request
		// by handing them to the settings layer as a variable. In the template,
		// they can be accessed as params.paramName
		$params = Craft::$app->getUrlManager()->getRouteParams();

		return $this->renderTemplate('sprout-core/sproutcore/_settings/index', [
			'plugin' => $this->plugin,
			'selectedSidebarItem' => $this->selectedSidebarItem,
			'params' => $params
		]);
	}

	/**
	 * Saves plugin settings
	 *
	 * @throws BadRequestHttpException
	 */
	public function actionSaveSettings()
	{
		$this->requirePostRequest();

		// the submitted settings
		$settings = Craft::$app->getRequest()->getBodyParam('settings');

		if (SproutCore::$app->settings->saveSettings($this->plugin, $settings))
		{
			Craft::$app->getSession()->setNotice(SproutCore::t('Settings saved.'));

			$this->redirectToPostedUrl();
		} else
		{
			Craft::$app->getSession()->setError(SproutCore::t('Couldn’t save settings.'));

			Craft::$app->getUrlManager()->setRouteParams([
				'settings' => $settings
			]);
		}
	}
}
