<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\config\base\Settings;
use Craft;

class CampaignsSettings extends Settings
{
    /**
     * @var string
     */
    public $pluginNameOverride = '';

    /**
     * @var bool
     */
    public $enableCampaignEmails = false;

    /**
     * @var null
     */
    public $emailTemplateId;

    /**
     * @var int
     */
    public $enablePerEmailEmailTemplateIdOverride = 0;

    public function getSettingsNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Campaigns'),
            'url' => 'sprout/settings/campaigns/campaign-types',
            'icon' => '@sproutbaseicons/plugins/campaigns/icon.svg',
            'subnav' => [
                'campaign-types' => [
                    'label' => Craft::t('sprout', 'Campaign Types'),
                    'url' => 'sprout/settings/campaigns/campaign-types',
                    'template' => 'sprout-base-campaigns/settings/campaign-types'
                ]
            ]
        ];
    }
}

