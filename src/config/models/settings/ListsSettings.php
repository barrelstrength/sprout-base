<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\config\base\Settings;
use Craft;

class ListsSettings extends Settings
{
    public function getSettingsNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Lists'),
            'url' => 'sprout/settings/lists',
            'icon' => '@sproutbaseicons/plugins/lists/icon.svg',
        ];
    }
}

