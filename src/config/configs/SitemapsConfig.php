<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\app\sitemaps\migrations\Install;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\SitemapsSettings;
use barrelstrength\sproutbase\SproutBase;
use Craft;

class SitemapsConfig extends Config
{
    public function createSettingsModel()
    {
        return new SitemapsSettings();
    }

    public function createInstallMigration()
    {
        return new Install();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Sitemaps'),
            'url' => 'sprout/sitemaps',
            'icon' => '@sproutbaseicons/plugins/sitemaps/icon-mask.svg'
        ];
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
            // Sitemaps
            'sprout/sitemaps/sitemaps/edit/<sitemapSectionId:\d+>/<siteHandle:[^\/]+\/?>' =>
                'sprout/sitemaps/sitemap-edit-template',
            'sprout/sitemaps/sitemaps/new/<siteHandle:[^\/]+\/?>' =>
                'sprout/sitemaps/sitemap-edit-template',
            'sprout/sitemaps/sitemaps/<siteHandle:[^\/]+\/?>' =>
                'sprout/sitemaps/sitemap-index-template',
            'sprout/sitemaps/sitemaps' =>
                'sprout/sitemaps/sitemap-index-template',
            'sprout/sitemaps' =>
                'sprout/sitemaps/sitemap-index-template'
        ];
    }

    /**
     * Match dynamic sitemap URLs
     *
     * Example matches include:
     *
     * Sitemap Index Page
     * - sitemap.xml
     *
     * URL-Enabled Sections
     * - sitemap-t6PLT5o43IFG-1.xml
     * - sitemap-t6PLT5o43IFG-2.xml
     *
     * Special Groupings
     * - sitemap-singles.xml
     * - sitemap-custom-pages.xml
     *
     * @return array
     */
    public function getSiteUrlRules(): array
    {
        // @todo - migration: probably need to update logic for SEO and Sitemaps support
        $settings = SproutBase::$app->settings->getSettingsByKey('sitemaps');

        if ($settings->enableDynamicSitemaps) {
            return [
                'sitemap-<sitemapKey:.*>-<pageNumber:\d+>.xml' =>
                    'sprout-base-sitemaps/xml-sitemap/render-xml-sitemap',
                'sitemap-?<sitemapKey:.*>.xml' =>
                    'sprout-base-sitemaps/xml-sitemap/render-xml-sitemap',
            ];
        }

        return [];
    }
}

