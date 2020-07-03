<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\app\sitemaps\controllers\SitemapsController;
use barrelstrength\sproutbase\app\sitemaps\controllers\XmlSitemapController;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\base\Settings;
use barrelstrength\sproutbase\config\models\settings\SitemapsSettings;
use barrelstrength\sproutbase\migrations\install\sitemaps\Install;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\helpers\ProjectConfig as ProjectConfigHelper;

class SitemapsConfig extends Config
{
    public static function getControllerMap(): array
    {
        return [
            'sitemaps' => SitemapsController::class,
            'xml-sitemap' => XmlSitemapController::class,
        ];
    }

    public static function getKey(): string
    {
        return 'sitemaps';
    }

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Sitemaps');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Manage XML sitemaps');
    }

    public function getConfigGroup()
    {
        return new SeoConfig();
    }

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
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:sitemaps:editSitemaps' => [
                'label' => Craft::t('sprout', 'Edit Sitemaps'),
            ],
        ];
    }

    public function getCpUrlRules(): array
    {
        return [
            'sprout/sitemaps/edit/<sitemapSectionId:\d+>' =>
                'sprout/sitemaps/sitemap-edit-template',
            'sprout/sitemaps/new' =>
                'sprout/sitemaps/sitemap-edit-template',
            'sprout/sitemaps' =>
                'sprout/sitemaps/sitemap-index-template',
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
        $settings = SproutBase::$app->settings->getSettingsByKey('sitemaps');

        if ($this->getEdition() === Config::EDITION_PRO && $settings->getIsEnabled()) {

            return [
                'sitemap-<sitemapKey:.*>-<pageNumber:\d+>.xml' =>
                    'sprout/xml-sitemap/render-xml-sitemap',
                'sitemap-?<sitemapKey:.*>.xml' =>
                    'sprout/xml-sitemap/render-xml-sitemap',
            ];
        }

        return [];
    }

    public function setEdition()
    {
        $sproutSitemapsIsPro = SproutBase::$app->config->isPluginEdition('sprout-sitemaps', Config::EDITION_STANDARD);
        $sproutSeoIsPro = SproutBase::$app->config->isPluginEdition('sprout-seo', Config::EDITION_PRO);

        if ($sproutSeoIsPro || $sproutSitemapsIsPro) {
            $this->_edition = Config::EDITION_PRO;
        }
    }

    public function setSettings(Settings $settings)
    {
        $settings->siteSettings = ProjectConfigHelper::unpackAssociativeArray($settings->siteSettings);
        $settings->groupSettings = ProjectConfigHelper::unpackAssociativeArray($settings->groupSettings);

        parent::setSettings($settings);
    }
}

