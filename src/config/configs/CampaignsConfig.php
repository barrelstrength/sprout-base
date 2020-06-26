<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\app\campaigns\controllers\CampaignEmailController;
use barrelstrength\sproutbase\app\campaigns\controllers\CampaignTypeController;
use barrelstrength\sproutbase\app\campaigns\mailers\CopyPasteMailer;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\CampaignsSettings;
use barrelstrength\sproutbase\migrations\campaigns\Install;
use barrelstrength\sproutbase\SproutBase;
use Craft;

class CampaignsConfig extends Config
{
    public static function getControllerMap(): array
    {
        return [
            'campaign-email' => CampaignEmailController::class,
            'campaign-type' => CampaignTypeController::class,
            'copy-paste' => CopyPasteMailer::class,
        ];
    }

    public static function getSproutConfigs(): array
    {
        return [
            EmailPreviewConfig::class,
            FieldsConfig::class
        ];
    }

    public static function getKey(): string
    {
        return 'campaigns';
    }

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Campaigns');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Manage and send email marketing campaigns');
    }

    public function getUpgradeMessage(): string
    {
        return Craft::t('sprout', 'Upgrade to Sprout Campaigns PRO to manage unlimited Campaigns using custom integrations.');
    }

    public function getConfigGroup()
    {
        return new NotificationsConfig();
    }

    public function createSettingsModel()
    {
        return new CampaignsSettings();
    }

    public function createInstallMigration()
    {
        return new Install();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Campaigns'),
            'url' => 'sprout/campaigns',
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:campaigns:editCampaigns' => [
                'label' => Craft::t('sprout', 'Edit Campaign Emails'),
                'nested' => [
                    'sprout:campaigns:sendCampaigns' => [
                        'label' => Craft::t('sprout', 'Send Campaign Emails'),
                    ],
                ],
            ],
        ];
    }

    public function getCpUrlRules(): array
    {
        return [
            'sprout/campaigns/<campaignTypeId:\d+>/<emailId:new>' =>
                'sprout/campaign-email/edit-campaign-email',
            'sprout/campaigns/edit/<emailId:\d+>' =>
                'sprout/campaign-email/edit-campaign-email',
            'sprout/campaigns' =>
                'sprout/campaign-email/campaign-email-index-template',

            // DB Settings
            'sprout/settings/<configKey:campaigns>/<subNavKey:campaign-types>/edit/<campaignTypeId:\d+>' => [
                'route' => 'sprout/campaign-type/edit-campaign-type',
            ],
        ];
    }

    public function setEdition()
    {
        $sproutEmailIsPro = SproutBase::$app->config->isPluginEdition('sprout-email', Config::EDITION_PRO);
        $sproutCampaignsIsPro = SproutBase::$app->config->isPluginEdition('sprout-campaigns', Config::EDITION_PRO);

        if ($sproutEmailIsPro || $sproutCampaignsIsPro) {
            $this->_edition = Config::EDITION_PRO;
        }
    }

    public function isUpgradable(): bool
    {
        return false;
    }
}

