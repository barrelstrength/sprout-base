<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\phonefield;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class PhoneFieldAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/phonefield/dist';

        $this->depends = [
            CpAsset::class
        ];

        $this->css = [
            'css/phone-field.css',
        ];

        $this->js = [
            'js/PhoneField.js'
        ];

        parent::init();
    }
}