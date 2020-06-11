<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\base;

use Craft;
use craft\base\Model;
use craft\errors\SiteNotFoundException;
use craft\helpers\StringHelper;
use craft\models\Site;
use ReflectionClass;
use ReflectionException;

/**
 *
 * @property array  $settingsNavItem
 * @property string $key
 */
abstract class Settings extends Model implements SettingsInterface
{
    protected $_currentSite;

    protected $_alternateName;

    protected $_enabledStatus;

    /**
     * @return Site
     * @throws SiteNotFoundException
     */
    public function getCurrentSite()
    {
        return $this->_currentSite ?? Craft::$app->getSites()->getPrimarySite();
    }

    /**
     * @param null $site
     *
     * @throws SiteNotFoundException
     */
    public function setCurrentSite($site = null)
    {
        $this->_currentSite = $site ?? Craft::$app->getSites()->getPrimarySite();
    }

    public function getAlternateName()
    {
        if (!empty($this->_alternateName)) {
            return $this->_alternateName;
        }

        return null;
    }

    public function setAlternateName($value)
    {
        $this->_alternateName = $value;
    }

    public function getEnabledStatus()
    {
        return $this->_enabledStatus;
    }

    public function setEnabledStatus($value)
    {
        $this->_enabledStatus = (int) $value;
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    public function getKey(): string
    {
        $class = new ReflectionClass($this);
        $baseName = preg_replace('/Settings$/', '', $class->getShortName());

        return StringHelper::toKebabCase($baseName);
    }

    public function getSettingsNavItem(): array
    {
        return [];
    }

    protected function beforeAddDefaultSettings()
    {
        return null;
    }
}

