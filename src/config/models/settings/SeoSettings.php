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
     * @var string
     */
    public $pluginNameOverride = '';

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

    /**
     * @deprecated
     *
     * This field is required on the Sprout SEO Settings model
     * for the migration m190415_000000_adds_sprout_redirects_migration
     * so that the structureId setting gets properly migrated.
     *
     * General usage of this setting has moved to the SproutBaseRedirects Settings model.
     */
//    public $structureId;

    public function getSettingsNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'SEO'),
            'url' => 'sprout/settings/seo',
            'icon' => '@sproutbaseicons/plugins/seo/icon.svg',
            'subnav' => [
                'meta' => [
                    'label' => Craft::t('sprout', 'Metadata'),
                    'url' => 'sprout/settings/seo/meta',
                    'template' => 'sprout-seo/settings/general'
                ]
            ]
        ];
    }
}

