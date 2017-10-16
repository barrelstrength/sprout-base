<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\web\assets\sproutcore\cp;

use craft\web\AssetBundle;

class CpAsset extends AssetBundle
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->sourcePath = '@sproutcore/web/assets/sproutcore/cp/dist';

		$this->css = [
			'css/sproutcp.css',
		];

		parent::init();
	}
}