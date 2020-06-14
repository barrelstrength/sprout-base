<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\migrations\email\Install;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\NotificationSettings;
use barrelstrength\sproutbase\SproutBase;
use Craft;

class NotificationsConfig extends Config
{
    public function getKey(): string
    {
        return 'notifications';
    }

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Notifications');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Manage and send notifications');
    }

    public static function groupName(): string
    {
        return Craft::t('sprout', 'Email');
    }

    public function createSettingsModel()
    {
        return new NotificationSettings();
    }

    public function createInstallMigration()
    {
        return new Install();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Notifications'),
            'url' => 'sprout/notifications'
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:notifications:viewNotifications' => [
                'label' => Craft::t('sprout', 'View Notifications'),
                'nested' => [
                    'sprout:notifications:editNotifications' => [
                        'label' => Craft::t('sprout', 'Edit Notification Emails')
                    ]
                ]
            ]
        ];
    }

    public function getCpUrlRules(): array
    {
        return [
            // Notifications
            'sprout/notifications/edit/<emailId:\d+|new>' =>
                'sprout/notifications/edit-notification-email-template',
            'sprout/notifications/settings/edit/<emailId:\d+|new>' =>
                'sprout/notifications/edit-notification-email-settings-template',
            'sprout/notifications' =>
                'sprout/notifications/notifications-index-template',

            // Preview
//            'sprout/notifications/preview/<emailId:\d+>' => [
//                'route' => 'sprout/notifications/preview'
//            ],
//            'sprout/notifications/preview/<emailId:\d+>' => [
//                'route' => 'sprout/sent-email/preview'
//            ],
        ];
    }

    public function setEdition()
    {
        $sproutEmailIsPro = SproutBase::$app->config->isPluginEdition('sprout-email', Config::EDITION_PRO);

        if ($sproutEmailIsPro) {
            $this->_edition = Config::EDITION_PRO;
        }
    }
}

