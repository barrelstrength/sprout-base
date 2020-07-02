<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\app\seo\controllers\GlobalMetadataController;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\SeoSettings;
use barrelstrength\sproutbase\migrations\metadata\Install;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\web\twig\variables\SeoVariable;
use Craft;

class SeoConfig extends Config
{
    public static function getControllerMap(): array
    {
        return [
            'global-metadata' => GlobalMetadataController::class,
        ];
    }

    public static function getVariableMap(): array
    {
        return [
            'seo' => SeoVariable::class,
        ];
    }

    public static function getSproutConfigDependencies(): array
    {
        return [
            FieldsConfig::class,
        ];
    }

    public static function getKey(): string
    {
        return 'seo';
    }

    public static function displayName(): string
    {
        return Craft::t('sprout', 'SEO');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Manage SEO metadata');
    }

    public function getUpgradeMessage(): string
    {
        return Craft::t('sprout', 'Upgrade to Sprout SEO PRO to enable multiple Metadata field mappings, manage redirects, and generate sitemaps.');
    }

    public function createSettingsModel()
    {
        return new SeoSettings();
    }

    public function createInstallMigration()
    {
        return new Install();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'SEO'),
            'url' => 'sprout/seo/globals',
            'subnav' => [
                'globals' => [
                    'label' => Craft::t('sprout', 'Globals'),
                    'url' => 'sprout/seo/globals',
                ],
            ],
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:seo:editGlobals' => [
                'label' => Craft::t('sprout', 'Edit Globals'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function getCpUrlRules(): array
    {
        return [
            'sprout/seo/globals/<selectedTabHandle:[^\/]+>' =>
                'sprout/global-metadata/edit-global-metadata',
            'sprout/seo/globals' =>
                'sprout/global-metadata/hello',
            'sprout/seo' =>
                'sprout/global-metadata/hello',
        ];
    }

    public function setEdition()
    {
        $sproutSeoIsPro = SproutBase::$app->config->isPluginEdition('sprout-seo', Config::EDITION_PRO);

        if ($sproutSeoIsPro) {
            $this->_edition = Config::EDITION_PRO;
        }
    }
}

