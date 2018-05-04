<?php

namespace barrelstrength\sproutbase\sproutbase\web\assets\groups;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class GroupsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbase/sproutbase/web/assets/groups/dist';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/groups.js',
        ];

        parent::init();
    }
}