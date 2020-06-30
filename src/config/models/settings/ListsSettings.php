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
    public $enableUserSync = false;

    public $enableAutoList = false;

    public function getSettingsNavItem(): array
    {
        return [
            'subscribers' => [
                'label' => Craft::t('sprout', 'Subscribers'),
                'template' => 'sprout/_settings/subscribers',
            ],
        ];
    }
}

