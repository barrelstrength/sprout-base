<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates;
use barrelstrength\sproutbase\config\base\Settings;
use Craft;

/**
 *
 * @property array $settingsNavItem
 */
class EmailSettings extends Settings
{
    /**
     * @var string
     */
    public $displayName = '';

    /**
     * @var bool
     */
    public $enableNotificationEmails = true;

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
                'template' => 'sprout-base-email/settings/mailers'
            ],
            'notifications' => [
                'label' => Craft::t('sprout', 'Notifications'),
                'template' => 'sprout-base-email/settings/notifications'
            ]
        ];
    }
}

