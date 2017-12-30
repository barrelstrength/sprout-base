<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\web\assets\sproutbase\cp;

use craft\web\AssetBundle;

class CpAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@sproutbase/web/assets/sproutbase/cp/dist';

        $this->css = [
            'css/sproutcp.css',
        ];

        parent::init();
    }
}