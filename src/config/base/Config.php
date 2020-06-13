<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\base;

use craft\base\Component;

abstract class Config extends Component implements ConfigInterface
{
    protected $_edition = 'lite';

    /**
     * @var Settings $_settings
     */
    protected $_settings;

    public function getEdition(): string
    {
        return $this->_edition;
    }

    public function setEdition()
    {
        $this->_edition = 'lite';
    }

    /**
     * @return Settings|null
     */
    public function getSettings()
    {
        return $this->_settings;
    }

    public function setSettings(Settings $settings)
    {
        $this->_settings = $settings;
    }

    public function hasControlPanelSettings(): bool
    {
        return true;
    }

    public static function groupName(): string
    {
        return static::displayName();
    }

    public function getConfigGroup()
    {
        return null;
    }

    public function getDescription(): string
    {
        return '';
    }

    public function createSettingsModel()
    {
        return null;
    }

    public function createInstallMigration()
    {
        return null;
    }

    public function getCpNavItem(): array
    {
        return [];
    }

    public function getCpUrlRules(): array
    {
        return [];
    }

    public function getSiteUrlRules(): array
    {
        return [];
    }

    public function getUserPermissions(): array
    {
        return [];
    }

    public function getSproutDependencies(): array
    {
        return [];
    }

    public function getConfigSettings(): array
    {
        return [];
    }
}

