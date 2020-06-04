<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\config\base\Settings;
use barrelstrength\sproutbase\SproutBase;
use Craft;

class SitemapsSettings extends Settings
{
    /**
     * @var string
     */
    public $pluginNameOverride = '';

    /**
     * @var bool
     */
    public $enableCustomSections = false;

    /**
     * @var bool
     */
    public $enableDynamicSitemaps = true;

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
        return [
            'label' => Craft::t('sprout', 'Sitemaps'),
            'url' => 'sprout/settings/sitemaps',
            'icon' => '@sproutbaseicons/plugins/sitemaps/icon.svg',
            'subnav' => [
                'sitemaps' => [
                    'label' => Craft::t('sprout', 'Sitemaps'),
                    'url' => 'sprout/settings/sitemaps',
                    'template' => 'sprout-base-sitemaps/settings/sitemaps'
                ]
            ]
        ];
    }
}

