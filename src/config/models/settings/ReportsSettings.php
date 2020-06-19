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
class ReportsSettings extends Settings
{
    /**
     * @var string
     */
    public $defaultPageLength = 10;

    /**
     * @var string
     */
    public $defaultExportDelimiter = ',';

    public function getSettingsNavItem(): array
    {
        return [
            'reports' => [
                'label' => Craft::t('sprout', 'Reports'),
                'template' => 'sprout/_settings/reports'
            ]
        ];
    }
}

