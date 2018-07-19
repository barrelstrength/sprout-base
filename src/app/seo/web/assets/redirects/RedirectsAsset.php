<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\seo\web\assets\redirects;


use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class RedirectsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/seo/web/assets/redirects/dist';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/redirectindex.js'
        ];

        parent::init();
    }
}