<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\SeoSettings;
use Craft;

class MetadataConfig extends Config
{
    public function createSettingsModel()
    {
        return new SeoSettings();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'SEO'),
            'url' => 'sprout/metadata/globals',
            'icon' => '@sproutbaseicons/plugins/seo/icon-mask.svg',
            'subnav' => [
                'reports' => [
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
            'sprout/metadata/globals/<selectedTabHandle:.*>/<siteHandle:.*>' =>
                'sprout/global-metadata/edit-global-metadata',
            'sprout/metadata/globals/<selectedTabHandle:.*>' =>
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

