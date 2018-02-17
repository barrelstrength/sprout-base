<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assets\sproutfields\email;

use craft\web\AssetBundle;
use barrelstrength\sproutbase\web\assets\sproutbase\cp\CpAsset;

class EmailFieldAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@sproutbase/web/assets/sproutfields';

        // define the dependencies
        $this->depends = [
            CpAsset::class
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'email/dist/js/sproutemailfield.js',
        ];

        $this->css = [
            'base/css/sproutfields.css',
        ];

        parent::init();
    }
}