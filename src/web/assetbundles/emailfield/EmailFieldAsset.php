<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\emailfield;

use \barrelstrength\sproutbase\web\assetbundles\sproutcp\SproutCpAsset;
use craft\web\AssetBundle;

class EmailFieldAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/emailfield/dist';

        $this->depends = [
            SproutCpAsset::class
        ];

        $this->css = [
            'css/email-field.css',
        ];

        $this->js = [
            'js/EmailField.js',
        ];

        parent::init();
    }
}