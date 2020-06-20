<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\addressfield;

use barrelstrength\sproutbase\web\assetbundles\sproutcp\SproutCpAsset;
use craft\web\AssetBundle;

class AddressFieldAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/addressfield/dist';

        $this->depends = [
            SproutCpAsset::class,
        ];

        $this->css = [
            'css/address-field.css',
        ];

        $this->js = [
            'js/AddressField.js',
        ];

        parent::init();
    }
}