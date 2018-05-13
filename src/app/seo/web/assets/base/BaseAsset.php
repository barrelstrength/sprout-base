<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\seo\web\assets\base;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @todo - move this Asset Bundle to Sprout Core.
 */
class BaseAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/seo/web/assets/base/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'css/sproutseo.css'
        ];

        $this->js = [
            // @todo - this has Section and Sitemap specific js
            'js/sproutseo.js'
        ];

        parent::init();
    }
}