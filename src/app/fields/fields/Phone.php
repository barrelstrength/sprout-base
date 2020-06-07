<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\fields;

use barrelstrength\sproutbase\app\fields\models\Phone as PhoneModel;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

/**
 *
 * @property array  $elementValidationRules
 * @property string $contentColumnType
 * @property mixed  $settingsHtml
 * @property array  $countries
 */
class Phone extends Field implements PreviewableFieldInterface
{
    /**
     * @var string|null
     */
    public $customPatternErrorMessage;

    /**
     * @var bool|null
     */
    public $limitToSingleCountry;

    /**
     * @var string|null
     */
    public $country;

    /**
     * @var string|null
     */
    public $placeholder;

    /**
     * @var string|null
     */
    public $customPatternToggle;

    public $mask;

    public $inputMask;

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Phone (Sprout Fields)');
    }

    /**
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return PhoneModel|mixed|null
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        return SproutBase::$app->phoneField->normalizeValue($this, $value, $element);
    }

    /**
     * @param                       $value
     *
     * @param ElementInterface|null $element
     *
     * @return array|mixed|string|null
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        $value = SproutBase::$app->phoneField->serializeValue($value);

        return parent::serializeValue($value, $element);
    }

    /**
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml()
    {
        return SproutBase::$app->phoneField->getSettingsHtml($this);
    }

    /**
     * @param mixed                 $value
     * @param ElementInterface|null $element
     *
     * @return string
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return SproutBase::$app->phoneField->getInputHtml($this, $value);
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        $rules = parent::getElementValidationRules();
        $rules[] = 'validatePhone';

        return $rules;
    }

    /**
     * Validates our fields submitted value beyond the checks
     * that were assumed based on the content attribute.
     *
     *
     * @param Element|ElementInterface $element
     *
     * @return void
     */
    public function validatePhone(ElementInterface $element)
    {
        $value = $element->getFieldValue($this->handle);

        // Don't run validation if a field is not required and has no value for the phone number
        if (!$this->required && empty($value->phone)) {
            return;
        }

        $isValid = SproutBase::$app->phoneField->validate($value);

        if (!$isValid) {
            $message = SproutBase::$app->phoneField->getErrorMessage($this, $value->country);
            $element->addError($this->handle, $message);
        }
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        $html = '';

        if ($value !== null && $value->international) {
            $fullNumber = $value->international;
            $html = '<a href="tel:'.$fullNumber.'" target="_blank">'.$fullNumber.'</a>';
        }

        return $html;
    }
}
