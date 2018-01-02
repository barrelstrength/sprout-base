<?php

namespace barrelstrength\sproutbase\assetbundles\notifications;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class NotificationAsset extends AssetBundle
{
	public function init()
	{
		$this->depends = [
			CpAsset::class,
		];
		
		$this->sourcePath = "@sproutbase/assetbundles/notifications/dist";

		$this->js = [
			'js/notification.js'
		];

		parent::init();
	}
}