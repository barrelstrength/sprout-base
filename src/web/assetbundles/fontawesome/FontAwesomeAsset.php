<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\fontawesome;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class FontAwesomeAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaselib/font-awesome';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'css/font-awesome.min.css',
        ];

        parent::init();
    }
}