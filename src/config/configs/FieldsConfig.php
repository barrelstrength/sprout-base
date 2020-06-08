<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\migrations\fields\Install;
use barrelstrength\sproutbase\config\base\Config;
use Craft;

class FieldsConfig extends Config
{
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Fields');
    }

    public function showCpDisplaySettings(): bool
    {
        return false;
    }

    public function createInstallMigration()
    {
        return new Install();
    }

    public function getSettingsNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Fields'),
            'url' => 'sprout/settings/fields',
            'icon' => '@sproutbaseicons/plugins/fields/icon.svg',
        ];
    }
}

