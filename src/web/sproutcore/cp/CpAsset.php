<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\web\sproutcore\cp;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class CpAsset extends AssetBundle
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->sourcePath = '@sproutcore/web/sproutcore/cp/dist';

		$this->css = [
			'css/sproutcp.css',
		];

		parent::init();
	}
}