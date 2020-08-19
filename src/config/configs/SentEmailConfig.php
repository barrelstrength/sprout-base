<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\app\sentemail\controllers\SentEmailController;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\SentEmailSettings;
use barrelstrength\sproutbase\migrations\install\SentEmailInstall;
use barrelstrength\sproutbase\SproutBase;
use Craft;

class SentEmailConfig extends Config
{
    public static function getControllerMap(): array
    {
        return [
            'sent-email' => SentEmailController::class,
        ];
    }

    public static function getSproutConfigDependencies(): array
    {
        return [
            EmailPreviewConfig::class,
            FieldsConfig::class,
        ];
    }

    public static function getKey(): string
    {
        return 'sent-email';
    }

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Sent Email');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Track sent emails and resend messages');
    }

    public function getUpgradeMessage(): string
    {
        return Craft::t('sprout', 'Upgrade to Sprout Sent Email PRO to manage Resend Emails.');
    }

    public function getConfigGroup()
    {
        return new NotificationsConfig();
    }

    public function createSettingsModel()
    {
        return new SentEmailSettings();
    }

    public function createInstallMigration()
    {
        return new SentEmailInstall();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Sent Email'),
            'url' => 'sprout/sent-email',
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:sentEmail:viewSentEmail' => [
                'label' => Craft::t('sprout', 'View Sent Email'),
                'nested' => [
                    'sprout:sentEmail:resendEmails' => [
                        'label' => Craft::t('sprout', 'Resend Sent Emails'),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getCpUrlRules(): array
    {
        return [
            // Sent Emails
            'sprout/sent-email/sent-email' =>
                'sprout/sent-email/sent-email-index-template',
            'sprout/sent-email' =>
                'sprout/sent-email/sent-email-index-template',
        ];
    }

    public function setEdition()
    {
        $sproutCampaignsIsPro = SproutBase::$app->config->isPluginEdition('sprout-campaigns', Config::EDITION_PRO);
        $sproutSentEmailIsPro = SproutBase::$app->config->isPluginEdition('sprout-sent-email', Config::EDITION_PRO);
        $sproutEmailIsPro = SproutBase::$app->config->isPluginEdition('sprout-email', Config::EDITION_PRO);

        if ($sproutEmailIsPro || $sproutSentEmailIsPro || $sproutCampaignsIsPro) {
            $this->_edition = Config::EDITION_PRO;
        }
    }
}

