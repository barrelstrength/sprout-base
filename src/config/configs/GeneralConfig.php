<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\GeneralSettings;
use Craft;

class GeneralConfig extends Config
{
    public function createSettingsModel()
    {
        return new GeneralSettings();
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:sitemaps:editSitemaps' => [
                'label' => Craft::t('sprout', 'Edit Sitemaps')
            ],
        ];
    }

    public function getCpUrlRules(): array
    {
        return [
            'sprout/settings/<settingsSectionHandle:[^\/]+>/<settingsSubSectionHandle:[^\/]+\/?>' =>
                'sprout/settings/edit-settings',
            'sprout/settings/<settingsSectionHandle:[^\/]+\/?>' =>
                'sprout/settings/edit-settings',
            'sprout/settings' =>
                'sprout/settings/hello',
            'sprout' =>
                'sprout/settings/hello'
        ];
    }
}

