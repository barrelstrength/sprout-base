<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\twig\variables;

use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\helpers\Template;

class SproutBaseVariable
{
	/**
	 * @return string
	 */
	public function getSvg($path)
	{
		$svg = Craft::getAlias($path);

		return Template::raw(file_get_contents($svg));
	}

	public function getAvailableEvents()
	{
		return SproutBase::$app->notifications->getAvailableEvents();
	}

	public function getEventSelectedOptions($event, $notificationEmail)
	{
		return SproutBase::$app->notifications->getEventSelectedOptions($event, $notificationEmail);
	}
}
