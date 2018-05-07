<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\sproutfields\web\assets\url;

use barrelstrength\sproutbase\sproutbase\web\assets\cp\CpAsset;
use craft\web\AssetBundle;

class UrlFieldAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@sproutbase/sproutfields/web/assets';

        // define the dependencies
        $this->depends = [
            CpAsset::class
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'url/dist/js/sprouturlfield.js',
        ];

        $this->css = [
            'base/css/sproutfields.css',
        ];

        parent::init();
    }
}