<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\models;

use Craft;
use craft\base\Model;

/**
 * @property bool $enableUserSync
 * @property bool $enableAutoList
 * @property array $settingsNavItems
 */
class Settings extends Model
{
    /**
     * @var string
     */
    public $pluginNameOverride;

    /**
     * @var bool
     */
    public $enableUserSync;

    /**
     * @var bool
     */
    public $enableAutoList;

    /**
     * @inheritdoc
     */
    public function getSettingsNavItems(): array
    {
        return [
            'settingsHeading' => [
                'heading' => Craft::t('sprout', 'Settings'),
            ],
            'general' => [
                'label' => Craft::t('sprout', 'General'),
                'url' => 'sprout/settings/lists/general',
                'selected' => 'general',
                'template' => 'sprout/lists/settings/general',
            ],
        ];
    }
}
