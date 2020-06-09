<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\dragula;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class DragulaAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaselib/dragula';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/dragula.min.js',
            'js/dom-autoscroller.min.js'
        ];

        $this->css = [
            'css/dragula.min.css'
        ];

        parent::init();
    }
}