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
            'label' => Craft::t('sprout', 'Email'),
            'url' => 'sprout/settings/email',
            'icon' => '@sproutbaseicons/plugins/email/icon.svg',
            'subnav' => [
                'mailers' => [
                    'label' => Craft::t('sprout', 'Mailers'),
                    'url' => 'sprout/settings/email/mailers',
                    'template' => 'sprout-base-email/settings/mailers'
                ],
                'notifications' => [
                    'label' => Craft::t('sprout', 'Notifications'),
                    'url' => 'sprout/settings/email/notifications',
                    'template' => 'sprout-base-email/settings/notifications'
                ]
            ]
        ];
    }
}

