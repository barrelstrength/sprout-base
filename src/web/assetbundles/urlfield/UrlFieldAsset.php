<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\urlfield;

use barrelstrength\sproutbase\web\assetbundles\sproutcp\SproutCpAsset;
use craft\web\AssetBundle;

class UrlFieldAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/urlfield/dist';

        $this->depends = [
            SproutCpAsset::class,
        ];

        $this->css = [
            'css/url-field.css',
        ];

        $this->js = [
            'js/UrlField.js',
        ];

        parent::init();
    }
}