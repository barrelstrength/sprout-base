<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\config\base\Settings;
use Craft;

class SentEmailSettings extends Settings
{
    /**
     * @var bool
     */
    public $enableSentEmails = false;

    /**
     * @var int
     */
    public $sentEmailsLimit = 5000;

    /**
     * @var int
     */
    public $cleanupProbability = 1000;

    public function getSettingsNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Sent Email'),
            'url' => 'sprout/settings/sent-email',
            'icon' => '@sproutbaseicons/plugins/sent-email/icon.svg',
            'subnav' => [
                'sent-email' => [
                    'label' => Craft::t('sprout', 'Sent Email'),
                    'url' => 'sprout/settings/sent-email',
                    'selected' => 'sent-email',
                    'template' => 'sprout-base-sent-email/settings/sent-email'
                ]
            ]
        ];
    }
}

