<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\seo\web\assets\schema;


use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SchemaAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/seo/web/assets/schema/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/schema.js'
        ];

        parent::init();
    }
}