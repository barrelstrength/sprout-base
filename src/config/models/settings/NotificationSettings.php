<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates;
use barrelstrength\sproutbase\config\base\Settings;
use barrelstrength\sproutbase\config\controllers\SettingsController;
use Craft;

class NotificationSettings extends Settings
{
    /**
     * @var null
     */
    public $emailTemplateId = BasicTemplates::class;

    /**
     * @var int
     */
    public $enablePerEmailEmailTemplateIdOverride = 0;

    public function getSettingsNavItem(): array
    {
        return [
            'mailers' => [
                'label' => Craft::t('sprout', 'Mailers'),
                'template' => 'sprout/_settings/mailers',
                'settingsTarget' => SettingsController::SETTINGS_TARGET_CUSTOM,
            ],
            'notifications' => [
                'label' => Craft::t('sprout', 'Notifications'),
                'template' => 'sprout/_settings/notifications',
            ],
        ];
    }
}

