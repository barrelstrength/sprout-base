<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\seo\web\assets\sitemaps;

use barrelstrength\sproutbase\app\seo\web\assets\base\BaseAsset;
use craft\web\AssetBundle;

class SitemapsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/seo/web/assets/sitemaps/dist';

        $this->depends = [
            BaseAsset::class
        ];

        // @todo - update this file to be named better
        $this->js = [
            'js/MetaTags.js'
        ];

        parent::init();
    }
}