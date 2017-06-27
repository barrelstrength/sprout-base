<?php

namespace barrelstrength\sproutcore;

use Craft;

abstract class SproutCoreHelper
{
	/**
	 * Register the Sprout Core module on the Craft::$app instance
	 *
	 * This should be called in the plugin init() method
	 */
	public static function registerModule()
	{
		if (!Craft::$app->hasModule('sprout-core')) {

			Craft::$app->setModule('sprout-core', SproutCore::class);

			// Have Craft load this module right away (so we can create templates)
			Craft::$app->getModule('sprout-core');
		}
	}
}
