<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\controllers;

use Craft;
use craft\errors\InvalidPluginException;
use craft\web\Controller as BaseController;
use function GuzzleHttp\debug_resource;

class SettingsController extends BaseController
{
	public function actionEditSettings()
	{
		$pluginHandle = Craft::$app->getRequest()->getSegment(1);
		$selectedSidebarItem = (Craft::$app->getRequest()->getSegment(3) == null)? 'general' : Craft::$app->getRequest()->getSegment(3);

		$plugin = Craft::$app->getPlugins()->getPlugin($pluginHandle);

		if (!$plugin) {
			throw new InvalidPluginException($pluginHandle);
		}

		return $this->renderTemplate('sprout-core/sproutcore/_settings/index', [
			'plugin' => $plugin,
			'selectedSidebarItem' => $selectedSidebarItem
		]);
	}
}
