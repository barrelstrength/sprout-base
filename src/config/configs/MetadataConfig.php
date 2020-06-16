<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\MetadataSettings;
use barrelstrength\sproutbase\migrations\metadata\Install;
use barrelstrength\sproutbase\SproutBase;
use Craft;

/**
 *
 * @property array $cpNavItem
 * @property array $cpUrlRules
 * @property string $description
 * @property array[]|array $userPermissions
 * @property string $key
 */
class MetadataConfig extends Config
{
    public function getKey(): string
    {
        return 'metadata';
    }

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Metadata');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Manage SEO metadata');
    }

    public static function groupName(): string
    {
        return Craft::t('sprout', 'SEO');
    }

    public function createSettingsModel()
    {
        return new MetadataSettings();
    }

    public function createInstallMigration()
    {
        return new Install();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'SEO'),
            'url' => 'sprout/metadata/globals',
            'subnav' => [
                'globals' => [
                    'label' => Craft::t('sprout', 'Globals'),
                    'url' => 'sprout/metadata/globals'
                ]
            ]
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:seo:editGlobals' => [
                'label' => Craft::t('sprout', 'Edit Globals')
            ]
        ];
    }

    /**
     * @return array
     */
    public function getCpUrlRules(): array
    {
        return [
            'sprout/metadata/globals/<selectedTabHandle:[^\/]+>/<siteHandle:[^\/]+>' =>
                'sprout/global-metadata/edit-global-metadata',
            'sprout/metadata/globals/<selectedTabHandle:[^\/]+>' =>
                'sprout/global-metadata/edit-global-metadata',
            'sprout/metadata/globals' => [
                'route' => 'sprout/global-metadata/edit-global-metadata',
                'params' => [
                    'selectedTabHandle' => 'website-identity'
                ]
            ],
            'sprout/metadata' => [
                'route' => 'sprout/global-metadata/edit-global-metadata',
                'params' => [
                    'selectedTabHandle' => 'website-identity'
                ]
            ],
        ];
    }

    public function setEdition()
    {
        $sproutSeoIsPro = SproutBase::$app->config->isPluginEdition('sprout-seo', Config::EDITION_PRO);

        if ($sproutSeoIsPro) {
            $this->_edition = Config::EDITION_PRO;
        }
    }

    public function getControllerMapKeys(): array
    {
        return [
            'global-metadata'
        ];
    }
}

