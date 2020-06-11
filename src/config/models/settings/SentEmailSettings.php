<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\config\base\Settings;
use Craft;

/**
 *
 * @property array $settingsNavItem
 */
class SentEmailSettings extends Settings
{
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
            'sent-email' => [
                'label' => Craft::t('sprout', 'Sent Email'),
                'template' => 'sprout/sentemail/settings/sent-email',
                'multisite' => true
            ]
        ];
    }
}

