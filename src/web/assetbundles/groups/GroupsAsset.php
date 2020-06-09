<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\assetbundles\groups;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class GroupsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseassetbundles/groups/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Groups.js',
        ];

        parent::init();
    }
}