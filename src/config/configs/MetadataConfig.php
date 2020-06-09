<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\MetadataSettings;
use barrelstrength\sproutbase\migrations\metadata\Install;
use Craft;

class MetadataConfig extends Config
{
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Metadata');
    }

    public static function groupName(): string
    {
        return Craft::t('sprout', 'SEO');
    }

    public function createSettingsModel()
    {
        return new MetadataSettings();
    }

    public function createInstallMigration()
    {
        return new Install();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'SEO'),
            'url' => 'sprout/metadata/globals',
            'subnav' => [
                'globals' => [
                    'label' => Craft::t('sprout', 'Globals'),
                    'url' => 'sprout/metadata/globals'
                ]
            ]
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:seo:editGlobals' => [
                'label' => Craft::t('sprout', 'Edit Globals')
            ]
        ];
    }

    /**
     * @return array
     */
    public function getCpUrlRules(): array
    {
        return [
            // Globals
            'sprout/metadata/globals/<selectedTabHandle:[^\/]+>/<siteHandle:[^\/]+>' =>
                'sprout/global-metadata/edit-global-metadata',
            'sprout/metadata/globals/<selectedTabHandle:[^\/]+>' =>
                'sprout/global-metadata/edit-global-metadata',
            'sprout/metadata/globals' => [
                'route' => 'sprout/global-metadata/edit-global-metadata',
                'params' => [
                    'selectedTabHandle' => 'website-identity'
                ]
            ],
            'sprout/metadata' => [
                'route' => 'sprout/global-metadata/edit-global-metadata',
                'params' => [
                    'selectedTabHandle' => 'website-identity'
                ]
            ],
        ];
    }
}

