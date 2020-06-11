<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\config\base\Settings;
use Craft;
use craft\errors\SiteNotFoundException;

/**
 *
 * @property array $settingsNavItem
 */
class SitemapsSettings extends Settings
{
    /**
     * @var bool
     */
    public $enableCustomSections = false;

    /**
     * @var bool
     */
    public $enableMultilingualSitemaps = false;

    /**
     * @var int
     */
    public $totalElementsPerSitemap = 500;

    /**
     * @var array
     */
    public $siteSettings = [];

    /**
     * @var array
     */
    public $groupSettings = [];

    public function getSettingsNavItem(): array
    {
        $subNav['sitemaps'] = [
            'label' => Craft::t('sprout', 'Sitemaps'),
            'template' => 'sprout/sitemaps/settings/sitemaps'
        ];

        if (Craft::$app->isMultiSite) {
            $subNav['sitemap-sites'] = [
                'label' => Craft::t('sprout', 'Sitemap Sites'),
                'template' => 'sprout/sitemaps/settings/sitemap-sites',
                'packAssociativeArrays' => true
            ];
        }

        return $subNav;
    }

    /**
     * @throws SiteNotFoundException
     */
    protected function beforeAddDefaultSettings()
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $this->siteSettings[$site->id] = $site->id;
    }
}

