<?php

namespace barrelstrength\sproutcore\web\addressfield;

use craft\web\AssetBundle;

class AddressFieldAsset extends AssetBundle
{
	public function init()
	{
		$this->sourcePath = '@sproutcore/web/addressfield/dist';

		$this->js = [
			'js/AddressBox.js',
			'js/AddressForm.js',
			'js/EditAddressModal.js'
		];

		parent::init();
	}
}