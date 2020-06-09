<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\sitemaps;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SitemapsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/sitemaps/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'css/sitemaps.css'
        ];

        $this->js = [
            'js/Sitemaps.js'
        ];

        parent::init();
    }
}