<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\app\fields\controllers\AddressController;
use barrelstrength\sproutbase\app\fields\controllers\FieldsController;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\migrations\install\fields\Install;
use Craft;

class FieldsConfig extends Config
{
    public static function getControllerMap(): array
    {
        return [
            'fields' => FieldsController::class,
            'fields-address' => AddressController::class,
        ];
    }

    public static function getKey(): string
    {
        return 'fields';
    }

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Fields');
    }

    public static function hasControlPanelSettings(): bool
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
        ];
    }

    public function isUpgradable(): bool
    {
        return false;
    }
}

