<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\SeoSettings;
use Craft;

class SeoConfig extends Config
{
    public function createSettingsModel()
    {
        return new SeoSettings();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'SEO'),
            'url' => 'sprout/seo',
            'icon' => '@sproutbaseicons/plugins/seo/icon-mask.svg',
            'subnav' => [
                'reports' => [
                    'label' => Craft::t('sprout', 'Globals'),
                    'url' => 'sprout/seo/globals'
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
            'sprout/seo/globals/<selectedTabHandle:.*>/<siteHandle:.*>' =>
                'sprout-seo/global-metadata/edit-global-metadata',
            'sprout/seo/globals/<selectedTabHandle:.*>' =>
                'sprout-seo/global-metadata/edit-global-metadata',
            'sprout/seo/globals' => [
                'route' => 'sprout-seo/global-metadata/edit-global-metadata',
                'params' => [
                    'selectedTabHandle' => 'website-identity'
                ]
            ],
            'sprout/seo' => [
                'route' => 'sprout-seo/global-metadata/edit-global-metadata',
                'params' => [
                    'selectedTabHandle' => 'website-identity'
                ]
            ],
        ];
    }
}

