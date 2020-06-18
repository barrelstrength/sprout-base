<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\config\base\Config;
use Craft;

/**
 *
 * @property string $key
 */
class EmailPreviewConfig extends Config
{
    public function getKey(): string
    {
        return 'email-preview';
    }

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Email Preview');
    }

    public function hasControlPanelSettings(): bool
    {
        return false;
    }

    public function isUpgradable(): bool
    {
        return false;
    }

    public function getCpUrlRules(): array
    {
        return [
            'sprout/preview/email/<emailId:\d+>' =>
                'sprout/email-preview/preview',
        ];
    }
}

