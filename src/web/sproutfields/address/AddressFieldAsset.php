<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\web\sproutfields\address;

use craft\web\AssetBundle;

class AddressFieldAsset extends AssetBundle
{
	public function init()
	{
		$this->sourcePath = '@sproutcore/web/sproutfields/address/dist';

		$this->js = [
			'js/AddressBox.js',
			'js/AddressForm.js',
			'js/EditAddressModal.js'
		];

		parent::init();
	}
}