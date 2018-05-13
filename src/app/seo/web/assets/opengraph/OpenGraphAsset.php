<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\seo\web\assets\opengraph;

use barrelstrength\sproutbase\app\seo\web\assets\base\BaseAsset;
use craft\web\AssetBundle;

class OpenGraphAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/seo/web/assets/opengraph/dist';

        $this->depends = [
            BaseAsset::class
        ];

        $this->js = [
            'js/open-graph.js'
        ];

        parent::init();
    }
}