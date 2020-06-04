<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\app\forms\migrations\Install;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\FormsSettings;
use Craft;

class FormsConfig extends Config
{
    public function createSettingsModel()
    {
        return new FormsSettings();
    }

//    public function createInstallMigration()
//    {
//        return new Install();
//    }
//
    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Forms'),
            'url' => 'sprout/forms',
            'icon' => '@sproutbaseicons/plugins/forms/icon-mask.svg',
            'subnav' => [
                'forms' => [
                    'label' => Craft::t('sprout', 'Forms'),
                    'url' => 'sprout/forms'
                ],
                'entries' => [
                    'label' => Craft::t('sprout', 'Entries'),
                    'url' => 'sprout/forms/entries'
                ],
            ]
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:forms:editForms' => [
                'label' => Craft::t('sprout', 'Edit Forms')
            ],
            'sprout:forms:viewEntries' => [
                'label' => Craft::t('sprout', 'View Form Entries'),
                'nested' => [
                    'sprout:forms:editEntries' => [
                        'label' => Craft::t('sprout', 'Edit Form Entries')
                    ]
                ]
            ]
        ];
    }

//    public function defineRules(): array
//    {
//        $rules = parent::defineRules();
//
//        $rules[] = [['formTemplateId'], 'required', 'on' => 'general'];
//
//        return $rules;
//    }

    /**
     * @return array
     */
    public function getCpUrlRules(): array
    {
        return [
            'sprout/forms' =>
                'sprout-forms/forms/forms-default-section',
            'sprout/forms' =>
                'sprout-forms/forms/forms-index-template',
            'sprout/forms/new' =>
                'sprout-forms/forms/edit-form-template',
            'sprout/forms/edit/<formId:\d+>' =>
                'sprout-forms/forms/edit-form-template',
            'sprout/forms/edit/<formId:\d+>/settings/<settingsSectionHandle:.*>' =>
                'sprout-forms/forms/edit-settings-template',
            'sprout/entries' =>
                'sprout-forms/entries/entries-index-template',
            'sprout/entries/edit/<entryId:\d+>' =>
                'sprout-forms/entries/edit-entry-template',
//            'sprout/settings/(general|advanced)' =>
//                'sprout-forms/settings/settings-index-template',

            'sprout/forms/<groupId:\d+>' =>
                'sprout-forms/forms',

            // NEW Settings
            '<settingsTarget:sprout-forms>/settings/<settingsSectionHandle:forms>/<settingsSubSectionHandle:entry-statuses>' =>
                'sprout/settings/edit-settings',
            '<settingsTarget:sprout-forms>/settings/<settingsSectionHandle:forms>/<settingsSubSectionHandle:entry-statuses>/new' =>
                'sprout-forms/entry-statuses/edit',
            '<settingsTarget:sprout-forms>/settings/<settingsSectionHandle:forms>/<settingsSubSectionHandle:entry-statuses>/<entryStatusId:\d+>' =>
                'sprout-forms/entry-statuses/edit',

            // Settings
//            'sprout-forms/settings/<settingsSectionHandle:.*>' =>
//                'sprout/settings/edit-settings',
//            'sprout-forms/settings' =>
//                'sprout/settings/edit-settings'
        ];
    }
}

