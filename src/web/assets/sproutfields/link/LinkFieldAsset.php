<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assets\sproutfields\link;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class LinkFieldAsset extends AssetBundle
{
	public function init()
	{
		// define the path that your publishable resources live
		$this->sourcePath = '@sproutbase/web/assets/sproutfields';

		// define the dependencies
		$this->depends = [
			CpAsset::class,
		];

		// define the relative path to CSS/JS files that should be registered with the page
		// when this asset bundle is registered
		$this->js = [
			'link/dist/js/sproutlinkfield.js',
		];

		$this->css = [
			'resources/css/sproutfields.css',
		];

		parent::init();
	}
}