<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\formentries;

use barrelstrength\sproutbase\web\assetbundles\sproutcp\SproutCpAsset;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class FormEntriesAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/formentries/dist';

        $this->depends = [
            CpAsset::class,
            SproutCpAsset::class,
        ];

        $this->css = [
            'css/charts.css'
        ];

        $this->js = [
            'js/FormEntriesIndex.js'
        ];

        parent::init();
    }
}