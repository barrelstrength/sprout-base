<?php

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
		$selectedSidebarItem = Craft::$app->getRequest()->getSegment(3);

		$plugin = Craft::$app->getPlugins()->getPlugin($pluginHandle);

		if (!$plugin) {
			throw new InvalidPluginException($pluginHandle);
		}

		return $this->renderTemplate('sprout-core/_settings/index', [
			'plugin' => $plugin,
			'selectedSidebarItem' => $selectedSidebarItem
		]);
	}
}