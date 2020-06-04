<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\config\base\Settings;
use Craft;

class GeneralSettings extends Settings
{
    public $campaignsPluginName = '';

    public $emailPluginName = '';

    public $formsPluginName = '';

    public $listsPluginName = '';

    public $reportsPluginName = '';

    public $redirectsPluginName = '';

    public $seoPluginName = '';

    public $sentEmailPluginName = '';

    public $sitemapsPluginName = '';

    public function getSettingsNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'General'),
            'url' => 'sprout/settings/general/navigation',
            'icon' => '@sproutbaseicons/plugins/icon.svg',
            'subnav' => [
                'navigation' => [
                    'label' => Craft::t('sprout', 'Navigation'),
                    'url' => 'sprout/settings/general/navigation',
                    'template' => 'sprout-base/_settings/navigation'
                ],
                'welcome' => [
                    'label' => Craft::t('sprout', 'Welcome'),
                    'url' => 'sprout/settings/general/welcome',
                    'template' => 'sprout-base/_settings/welcome'
                ],
            ]
        ];
    }
}

