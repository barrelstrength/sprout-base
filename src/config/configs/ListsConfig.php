<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\ListsSettings;
use Craft;

class ListsConfig extends Config
{
    public function createSettingsModel()
    {
        return new ListsSettings();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Lists'),
            'url' => 'sprout/lists/subscribers',
            'icon' => '@sproutbaseicons/plugins/lists/icon-mask.svg',
            'subnav' => [
                'subscribers' => [
                    'label' => Craft::t('sprout', 'Subscribers'),
                    'url' => 'sprout/lists/subscribers'
                ],
                'lists' => [
                    'label' => Craft::t('sprout', 'Lists'),
                    'url' => 'sprout/lists/lists'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getUserPermissions(): array
    {
        return [
            'sprout:lists:editSubscribers' => [
                'label' => Craft::t('sprout', 'Edit Subscribers')
            ],
            'sprout:lists:editLists' => [
                'label' => Craft::t('sprout', 'Edit Lists')
            ]
        ];
    }

    public function getCpUrlRules(): array
    {
        return [
            'sprout-lists' =>
                'sprout-lists/lists/lists-index-template',

            // Subscribers
            'sprout-lists/subscribers/new' =>
                'sprout-lists/subscribers/edit-subscriber-template',
            'sprout-lists/subscribers/edit/<id:\d+>' =>
                'sprout-lists/subscribers/edit-subscriber-template',
            'sprout-lists/subscribers/<listHandle:[^\/]+\/?>' => [
                'template' => 'sprout-lists/subscribers'
            ],
            'sprout-lists/subscribers' =>
                'sprout-lists/subscribers/subscribers-index-template',

            // Lists
            'sprout-lists/lists' =>
                'sprout-lists/lists/lists-index-template',
            'sprout-lists/lists/new' =>
                'sprout-lists/lists/list-edit-template',
            'sprout-lists/lists/edit/<listId:\d+>' =>
                'sprout-lists/lists/list-edit-template',
        ];
    }
}

