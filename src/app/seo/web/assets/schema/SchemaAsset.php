<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\seo\web\assets\schema;

use barrelstrength\sproutbase\app\seo\web\assets\base\BaseAsset;
use barrelstrength\sproutbase\app\seo\web\assets\opengraph\OpenGraphAsset;
use barrelstrength\sproutbase\app\seo\web\assets\tageditor\TagEditorAsset;
use barrelstrength\sproutbase\app\seo\web\assets\twittercard\TwitterCardAsset;
use craft\web\AssetBundle;

class SchemaAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/app/seo/web/assets/schema/dist';

        $this->js = [
            'js/schema.js'
        ];

        parent::init();
    }
}