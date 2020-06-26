<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\services;

use barrelstrength\sproutbase\app\forms\base\Captcha;
use barrelstrength\sproutbase\app\forms\elements\Form;
use barrelstrength\sproutbase\app\forms\elements\Form as FormElement;
use barrelstrength\sproutbase\app\forms\events\OnBeforeValidateEntryEvent;
use barrelstrength\sproutbase\SproutBase;
use craft\events\RegisterComponentTypesEvent;
use yii\base\Component;
use Craft;

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
            'types' => [],
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

    public function handleFormCaptchasEvent(OnBeforeValidateEntryEvent $event)
    {
        if (!Craft::$app->getRequest()->getIsSiteRequest()) {
            return;
        }

        $enableCaptchas = (int)$event->form->enableCaptchas;

        // Don't process captchas if the form is set to ignore them
        if (!$enableCaptchas) {
            return;
        }

        /** @var Captcha[] $captchas */
        $captchas = SproutBase::$app->formCaptchas->getAllEnabledCaptchas();

        foreach ($captchas as $captcha) {
            $captcha->verifySubmission($event);
            $event->entry->addCaptcha($captcha);
        }
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
