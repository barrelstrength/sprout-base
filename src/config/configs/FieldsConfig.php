<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\migrations\fields\Install;
use Craft;

/**
 *
 * @property array $settingsNavItem
 * @property string $key
 */
class FieldsConfig extends Config
{
    public function getKey(): string
    {
        return 'fields';
    }

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Fields');
    }

    public function hasControlPanelSettings(): bool
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
            'url' => 'sprout/settings/fields'
        ];
    }

    public function isUpgradable(): bool
    {
        return false;
    }
}

