<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutfields\web\assets\regularexpression;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class RegularExpressionFieldAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@sproutbase/app/fields/web/assets';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'regularexpression/dist/js/sproutregularexpressionfield.js',
        ];

        $this->css = [
            'base/css/sproutfields.css',
        ];

        parent::init();
    }
}