<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\config\base\Settings;
use Craft;

class SeoSettings extends Settings
{
    /**
     * @var bool
     */
    public $displayFieldHandles = false;

    /**
     * @var bool
     */
    public $enableRenderMetadata = true;

    /**
     * @var bool
     */
    public $useMetadataVariable = false;

    /**
     * @var string
     */
    public $metadataVariableName = 'metadata';

    /**
     * @var int
     */
    public $maxMetaDescriptionLength = 160;

    public function getSettingsNavItem(): array
    {
        return [
            'meta' => [
                'label' => Craft::t('sprout', 'Metadata'),
                'template' => 'sprout/_settings/metadata'
            ]
        ];
    }
}

