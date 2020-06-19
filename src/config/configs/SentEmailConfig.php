<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\SentEmailSettings;
use barrelstrength\sproutbase\migrations\sentemail\Install;
use barrelstrength\sproutbase\SproutBase;
use Craft;

/**
 *
 * @property array $cpNavItem
 * @property array|string[] $cpUrlRules
 * @property NotificationsConfig $configGroup
 * @property string $description
 * @property array[]|array $userPermissions
 * @property array|string[] $controllerMapKeys
 * @property string $key
 */
class SentEmailConfig extends Config
{
    public function getKey(): string
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
        return new Install();
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
            'sprout:email:viewSentEmail' => [
                'label' => Craft::t('sprout', 'View Sent Email'),
                'nested' => [
                    'sprout:email:resendEmails' => [
                        'label' => Craft::t('sprout', 'Resend Sent Emails')
                    ]
                ]
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
        $sproutSentEmailIsPro = SproutBase::$app->config->isPluginEdition('sprout-sent-email', Config::EDITION_PRO);
        $sproutEmailIsPro = SproutBase::$app->config->isPluginEdition('sprout-email', Config::EDITION_PRO);

        if ($sproutEmailIsPro || $sproutSentEmailIsPro) {
            $this->_edition = Config::EDITION_PRO;
        }
    }

    public function getControllerMapKeys(): array
    {
        return [
            'sent-email'
        ];
    }
}

