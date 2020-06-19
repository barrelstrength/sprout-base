<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\services;

use barrelstrength\sproutbase\app\forms\base\FormTemplates;
use barrelstrength\sproutbase\app\forms\base\Integration;
use barrelstrength\sproutbase\app\forms\elements\Form;
use barrelstrength\sproutbase\app\forms\elements\Form as FormElement;
use barrelstrength\sproutbase\app\forms\errors\FormTemplatesDirectoryNotFoundException;
use barrelstrength\sproutbase\app\forms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutbase\app\forms\formtemplates\CustomTemplates;
use barrelstrength\sproutbase\app\forms\records\Form as FormRecord;
use barrelstrength\sproutbase\app\forms\records\Integration as IntegrationRecord;
use barrelstrength\sproutbase\app\forms\rules\FieldRule;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\migrations\forms\CreateFormContentTable;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\db\Query;
use craft\errors\MissingComponentException;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\MigrationHelper;
use craft\helpers\StringHelper;
use Throwable;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\Transaction;
use yii\web\BadRequestHttpException;

class FormCaptchas extends Component
{
    const EVENT_REGISTER_CAPTCHAS = 'registerSproutFormsCaptchas';

    /**
     * Returns all available Captcha classes
     *
     * @return array
     */
    public function getAllCaptchaTypes(): array
    {
        $event = new RegisterComponentTypesEvent([
            'types' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_CAPTCHAS, $event);

        return $event->types;
    }

    /**
     * @return array
     */
    public function getAllCaptchas(): array
    {
        $captchaTypes = $this->getAllCaptchaTypes();
        $captchas = [];

        foreach ($captchaTypes as $captchaType) {
            $captchas[$captchaType] = new $captchaType();
        }

        return $captchas;
    }

    /**
     * @return array
     */
    public function getAllEnabledCaptchas(): array
    {
        $sproutFormsSettings = SproutBase::$app->settings->getSettingsByKey('forms');
        $captchaTypes = $this->getAllCaptchas();
        $captchas = [];

        foreach ($captchaTypes as $captchaType) {
            $isEnabled = $sproutFormsSettings->captchaSettings[get_class($captchaType)]['enabled'] ?? false;
            if ($isEnabled) {
                $captchas[get_class($captchaType)] = $captchaType;
            }
        }

        return $captchas;
    }

    /**
     * @param FormElement $form
     *
     * @return string
     */
    public function getCaptchasHtml(Form $form): string
    {
        $captchas = $this->getAllEnabledCaptchas();
        $captchaHtml = '';

        foreach ($captchas as $captcha) {
            $captcha->form = $form;
            $captchaHtml .= $captcha->getCaptchaHtml();
        }

        return $captchaHtml;
    }
}
