<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\redirects;


use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class RedirectsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/redirects/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'css/redirects.css'
        ];

        $this->js = [
            'js/RedirectIndex.js'
        ];

        parent::init();
    }
}