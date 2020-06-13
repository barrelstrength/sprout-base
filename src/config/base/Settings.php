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
use craft\models\Site;

/**
 * @property array $settingsNavItem
 * @property string $key
 */
abstract class Settings extends Model implements SettingsInterface
{
    /**
     * @var Site $_currentSite
     */
    protected $_currentSite;

    /**
     * @var string
     */
    protected $_alternateName = '';

    /**
     * @var bool
     */
    protected $_isEnabled = true;

    /**
     * @return Site
     * @throws SiteNotFoundException
     */
    public function getCurrentSite(): Site
    {
        return $this->_currentSite ?? Craft::$app->getSites()->getPrimarySite();
    }

    /**
     * @param Site $site
     */
    public function setCurrentSite(Site $site = null)
    {
        $this->_currentSite = $site;
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

    public function getIsEnabled()
    {
        return $this->_isEnabled;
    }

    public function setIsEnabled($value)
    {
        $this->_isEnabled = (int)$value;
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

