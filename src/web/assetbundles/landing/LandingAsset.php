<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\landing;

use barrelstrength\sproutbase\web\assetbundles\sproutcp\SproutCpAsset;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class LandingAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/landing/dist';

        $this->depends = [
            CpAsset::class,
            SproutCpAsset::class
        ];

        $this->css = [
            'css/landing.css',
        ];

        parent::init();
    }
}