<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\base;

use craft\base\Component;
use craft\helpers\UrlHelper;

/**
 *
 * @property null|Settings $settings
 * @property array $cpNavItem
 * @property string $upgradeUrl
 * @property null $configGroup
 * @property string $description
 * @property string $edition
 * @property string $alternateName
 * @property array $userPermissions
 * @property array $configSettings
 * @property array $cpUrlRules
 * @property string $baseUrl
 * @property string $name
 * @property array $sproutDependencies
 * @property array $siteUrlRules
 * @property array $controllerMapKeys
 * @property bool $isPro
 */
abstract class Config extends Component implements ConfigInterface
{
    protected $_edition;

    /**
     * @var Settings $_settings
     */
    protected $_settings;

    /**
     * @var string
     */
    protected $_alternateName = '';

    public function getEdition()
    {
        if (!$this->_edition) {
            $this->setEdition();
        }

        return $this->_edition;
    }

    public function setEdition()
    {
        $this->_edition = 'lite';
    }

    public function getIsPro(): bool
    {
        return $this->_edition === self::EDITION_PRO;
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

    /**
     * @return string
     */
    public function getAlternateName(): string
    {
        if (!empty($this->_alternateName)) {
            return $this->_alternateName;
        }

        return '';
    }

    public function setAlternateName($value)
    {
        $this->_alternateName = $value;
    }

    public function hasControlPanelSettings(): bool
    {
        return true;
    }

    public function getBaseUrl(): string
    {
        return UrlHelper::cpUrl('sprout/'.$this->getKey());
    }

    public function getName(): string
    {
        if ($this->getAlternateName()) {
            return $this->getAlternateName();
        }

        return static::displayName();
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

    /**
     * Returns a list of keys that map to controller names
     * These will be used to disable a modules controller routes.
     *
     * @return array
     */
    public function getControllerMapKeys(): array
    {
        return [];
    }

    public function isUpgradable(): bool
    {
        return $this->getEdition() !== self::EDITION_PRO;
    }

    public function getUpgradeUrl(): string
    {
        return UrlHelper::cpUrl('sprout/upgrade/'.$this->getKey());
    }
}

