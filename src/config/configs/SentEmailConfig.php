<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\app\sentemail\migrations\Install;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\SentEmailSettings;
use Craft;

class SentEmailConfig extends Config
{
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Sent Email');
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
            'subnav' => [
                'reports' => [
                    'label' => Craft::t('sprout', 'Sent Emails'),
                    'url' => 'sprout/sent-email/sent-email'
                ],
            ]
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:sent-email:viewSentEmail' => [
                'label' => Craft::t('sprout', 'View Sent Email'),
                'nested' => [
                    'sprout:sent-email:resendEmails' => [
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

            // Preview
            'sprout/sent-email/preview/<emailId:\d+>' =>
                'sprout/sent-email/preview'
        ];
    }
}

