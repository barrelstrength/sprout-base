<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\app\email\controllers\MailersController;
use barrelstrength\sproutbase\app\email\controllers\NotificationsController;
use barrelstrength\sproutbase\app\email\events\notificationevents\EntriesDelete;
use barrelstrength\sproutbase\app\email\events\notificationevents\EntriesSave;
use barrelstrength\sproutbase\app\email\events\notificationevents\Manual;
use barrelstrength\sproutbase\app\email\events\notificationevents\UsersActivate;
use barrelstrength\sproutbase\app\email\events\notificationevents\UsersDelete;
use barrelstrength\sproutbase\app\email\events\notificationevents\UsersLogin;
use barrelstrength\sproutbase\app\email\events\notificationevents\UsersSave;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\NotificationSettings;
use barrelstrength\sproutbase\migrations\email\Install;
use barrelstrength\sproutbase\SproutBase;
use Craft;

class NotificationsConfig extends Config
{
    public static function getControllerMap(): array
    {
        return [
            'mailers' => MailersController::class,
            'notifications' => NotificationsController::class,
        ];
    }

    public static function getKey(): string
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
            'url' => 'sprout/notifications',
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:notifications:viewNotifications' => [
                'label' => Craft::t('sprout', 'View Notification Emails'),
                'nested' => [
                    'sprout:notifications:editNotifications' => [
                        'label' => Craft::t('sprout', 'Edit Notification Emails'),
                    ],
                ],
            ],
        ];
    }

    public function getCpUrlRules(): array
    {
        return [
            'sprout/notifications/edit/<emailId:\d+|new>' =>
                'sprout/notifications/edit-notification-email-template',
            'sprout/notifications/settings/edit/<emailId:\d+|new>' =>
                'sprout/notifications/edit-notification-email-settings-template',
            'sprout/notifications' =>
                'sprout/notifications/notifications-index-template',
        ];
    }

    public function setEdition()
    {
        $sproutEmailIsPro = SproutBase::$app->config->isPluginEdition('sprout-email', Config::EDITION_PRO);

        if ($sproutEmailIsPro) {
            $this->_edition = Config::EDITION_PRO;
        }
    }

    public function getSupportedNotificationEventTypes(): array
    {
        if ($this->getIsPro()) {
            return [
                EntriesDelete::class,
                EntriesSave::class,
                Manual::class,
                UsersActivate::class,
                UsersDelete::class,
                UsersLogin::class,
                UsersSave::class,
            ];
        }

        return [
            EntriesSave::class,
        ];
    }
}

