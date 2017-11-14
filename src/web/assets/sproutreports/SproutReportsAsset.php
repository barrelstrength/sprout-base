<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assets\sproutreports;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SproutReportsAsset extends AssetBundle
{
	public function init()
	{
		$this->sourcePath = "@sproutbase/web/assets/sproutreports/dist";

		$this->depends = [
			CpAsset::class,
		];

		$this->css = [
			'css/styles.css'
		];

		parent::init();
	}
}