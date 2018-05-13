<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\seo\web\assets\twittercard;

use barrelstrength\sproutbase\app\seo\web\assets\base\BaseAsset;
use craft\web\AssetBundle;

class TwitterCardAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/seo/web/assets/twittercard/dist';

        $this->depends = [
            BaseAsset::class
        ];

        $this->js = [
            'js/twitter-card.js'
        ];

        parent::init();
    }
}

