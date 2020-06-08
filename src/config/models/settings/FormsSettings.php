<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\app\forms\captchas\DuplicateCaptcha;
use barrelstrength\sproutbase\app\forms\captchas\HoneypotCaptcha;
use barrelstrength\sproutbase\app\forms\captchas\JavascriptCaptcha;
use barrelstrength\sproutbase\app\forms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutbase\config\base\Settings;
use barrelstrength\sproutbase\config\controllers\SettingsController;
use barrelstrength\sproutbase\SproutBase;
use Craft;

/**
 *
 * @property array            $settingsNavItem
 * @property array|string[][] $spamRedirectBehaviorsAsOptions
 */
class FormsSettings extends Settings
{
    const SPAM_REDIRECT_BEHAVIOR_NORMAL = 'redirectAsNormal';
    const SPAM_REDIRECT_BEHAVIOR_BACK_TO_FORM = 'redirectBackToForm';

    public $displayName = '';

    public $defaultSection = 'entries';

    public $formTemplateId = AccessibleTemplates::class;

    public $enableSaveData = true;

    public $saveSpamToDatabase = false;

    public $enableSaveDataDefaultValue = true;

    public $spamRedirectBehavior = self::SPAM_REDIRECT_BEHAVIOR_NORMAL;

    public $spamLimit = 500;

    public $cleanupProbability = 1000;

    public $trackRemoteIp = false;

    public $captchaSettings = [];

    public $enableEditFormEntryViaFrontEnd = false;

    public $showNotificationsTab = true;

    public $showReportsTab = true;

    public function getSettingsNavItem(): array
    {
        return [
            'forms' => [
                'label' => Craft::t('sprout', 'Forms'),
                'template' => 'sprout/forms/settings/forms',
            ],
            'spam-protection' => [
                'label' => Craft::t('sprout', 'Spam Protection'),
                'template' => 'sprout/forms/settings/spam-protection',
                'variables' => [
                    'spamRedirectBehaviorOptions' => $this->getSpamRedirectBehaviorsAsOptions()
                ]
            ],
            'entry-statuses' => [
                'label' => Craft::t('sprout', 'Entry Statuses'),
                'template' => 'sprout/forms/settings/entrystatuses',
                'settingsTarget' => SettingsController::SETTINGS_TARGET_DB,
                'actionButtonTemplate' => 'sprout/forms/settings/entrystatuses/_actionStatusButton',
                'variables' => [
                    'entryStatuses' => SproutBase::$app->entryStatuses->getAllEntryStatuses()
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getSpamRedirectBehaviorsAsOptions(): array
    {
        return [
            [
                'label' => 'Redirect as normal (recommended)',
                'value' => self::SPAM_REDIRECT_BEHAVIOR_NORMAL
            ],
            [
                'label' => 'Redirect back to form',
                'value' => self::SPAM_REDIRECT_BEHAVIOR_BACK_TO_FORM
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

    public function beforeAddDefaultSettings()
    {
        $this->captchaSettings = [
            DuplicateCaptcha::class => [
                'enabled' => 0
            ],
            JavascriptCaptcha::class => [
                'enabled' => 1
            ],
            HoneypotCaptcha::class => [
                'enabled' => 0,
                'honeypotFieldName' => 'sprout-forms-hc',
                'honeypotScreenReaderMessage' => 'Leave this field blank'
            ],
        ];
    }
}

