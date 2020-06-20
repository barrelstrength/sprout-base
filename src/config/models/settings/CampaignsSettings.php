<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\config\base\Settings;
use barrelstrength\sproutbase\config\controllers\SettingsController;
use Craft;

class CampaignsSettings extends Settings
{
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
                'template' => 'sprout/_settings/campaign-types',
                'settingsTarget' => SettingsController::SETTINGS_TARGET_DB,
            ],
        ];
    }
}

