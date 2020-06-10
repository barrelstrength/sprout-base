<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\base;

use craft\base\Component;
use craft\helpers\StringHelper;
use ReflectionClass;
use ReflectionException;

abstract class Config extends Component implements ConfigInterface
{
    protected $_edition = 'lite';

    protected $_settings;

    public function getEdition(): string
    {
        return $this->_edition;
    }

    public function setEdition($value)
    {
        $this->_edition = $value;
    }

    public function getSettings(): array
    {
        return $this->_settings;
    }

    public function addSettings($settings)
    {
        $this->_settings = $settings;
    }

    public function showCpDisplaySettings(): bool
    {
        return true;
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    public function getKey(): string
    {
        $class = new ReflectionClass($this);
        $baseName = preg_replace('/Config$/', '', $class->getShortName());

        return StringHelper::toKebabCase($baseName);
    }

    public static function groupName(): string
    {
        return static::displayName();
    }

    /**
     * The heading that this config will be grouped under
     * Defaults to itself. Other configs can use the same group heading
     * if they want want their sub-navs to be merged with a given group.
     *
     * @return ConfigInterface|null
     */
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

