<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\web\sproutfields\selectotherfield;

use barrelstrength\sproutseo\web\assets\base\BaseAsset;
use craft\web\AssetBundle;

class SelectOtherFieldAsset extends AssetBundle
{
	public function init()
	{
		$this->sourcePath = '@sproutcore/web/sproutfields/selectotherfield/dist';

		$this->depends = [
			BaseAsset::class
		];

		// @todo - update this file to be named better
		$this->js = [
			'js/sproutfields.js',
			'js/EditableTable.js'
		];

		parent::init();
	}
}