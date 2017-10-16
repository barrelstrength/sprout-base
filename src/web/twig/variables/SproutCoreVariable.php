<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\web\twig\variables;

use Craft;
use craft\helpers\Template;

class SproutCoreVariable
{
	/**
	 * @return string
	 */
	public function getSvg($path)
	{
		$svg = Craft::getAlias($path);

		return Template::raw(file_get_contents($svg));
	}
}
