<?php

namespace barrelstrength\sproutcore\web\assets\sproutreports;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class ReportCoreAsset extends AssetBundle
{
	public function init()
	{
		$this->sourcePath = "@sproutcore/resources/sproutreports";

		$this->depends = [
			CpAsset::class,
		];

		$this->css = [
			'css/styles.css'
		];

		parent::init();
	}
}