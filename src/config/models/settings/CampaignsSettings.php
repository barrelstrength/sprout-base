<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\config\base\Settings;
use Craft;

/**
 *
 * @property array $settingsNavItem
 */
class CampaignsSettings extends Settings
{
    /**
     * @var string
     */
    public $displayName = '';

    /**
     * @var bool
     */
    public $enableCampaignEmails = false;

    /**
     * @var int
     */
    public $emailTemplateId;

    /**
     * @var int
     */
    public $enablePerEmailEmailTemplateIdOverride = 0;

    public function getSettingsNavItem(): array
    {
        return [
            'campaign-types' => [
                'label' => Craft::t('sprout', 'Campaign Types'),
                'template' => 'sprout-base-campaigns/settings/campaign-types'
            ]
        ];
    }
}

