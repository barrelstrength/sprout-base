<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\app\lists\controllers\ListsController;
use barrelstrength\sproutbase\app\lists\controllers\SubscribersController;
use barrelstrength\sproutbase\app\lists\integrations\sproutreports\datasources\SubscriberListDataSource;
use barrelstrength\sproutbase\app\lists\web\twig\variables\ListsVariable;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\ListsSettings;
use barrelstrength\sproutbase\migrations\lists\Install;
use Craft;

class ListsConfig extends Config
{
    public static function getControllerMap(): array
    {
        return [
            'lists' => ListsController::class,
            'subscribers' => SubscribersController::class,
        ];
    }

    public static function getVariableMap(): array
    {
        return [
            'lists' => ListsVariable::class,
        ];
    }

    public static function getKey(): string
    {
        return 'lists';
    }

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Lists');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Manage lists and subscribers');
    }

    public function createSettingsModel()
    {
        return new ListsSettings();
    }

    public function createInstallMigration()
    {
        return new Install();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Lists'),
            'url' => 'sprout/lists/subscribers',
            'subnav' => [
                'subscribers' => [
                    'label' => Craft::t('sprout', 'Subscribers'),
                    'url' => 'sprout/lists/subscribers',
                ],
                'lists' => [
                    'label' => Craft::t('sprout', 'Lists'),
                    'url' => 'sprout/lists/lists',
                ],
            ],
        ];
    }

    public function getCpUrlRules(): array
    {
        return [
            // Subscribers
            'sprout/lists/subscribers/new' =>
                'sprout/subscribers/edit-subscriber-template',
            'sprout/lists/subscribers/edit/<id:\d+>' =>
                'sprout/subscribers/edit-subscriber-template',

            // @todo - should this be a controller route?
            'sprout/lists/subscribers/<listHandle:[^\/]+>' => [
                'template' => 'sprout/lists/subscribers',
            ],
            'sprout/lists/subscribers' =>
                'sprout/subscribers/subscribers-index-template',

            // Lists
            'sprout/lists' =>
                'sprout/lists/lists-index-template',
            'sprout/lists/new' =>
                'sprout/lists/list-edit-template',
            'sprout/lists/edit/<listId:\d+>' =>
                'sprout/lists/list-edit-template',
        ];
    }

    public function isUpgradable(): bool
    {
        return false;
    }

    public function getSupportedDataSourceTypes(): array
    {
        return [
            SubscriberListDataSource::class,
        ];
    }
}

