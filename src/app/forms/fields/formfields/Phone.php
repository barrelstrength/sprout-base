<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\fields\formfields;

use barrelstrength\sproutbase\app\fields\models\Phone as PhoneModel;
use barrelstrength\sproutbase\app\forms\base\FormField;
use barrelstrength\sproutbase\app\forms\elements\Entry;
use barrelstrength\sproutbase\app\forms\rules\conditions\ContainsCondition;
use barrelstrength\sproutbase\app\forms\rules\conditions\DoesNotContainCondition;
use barrelstrength\sproutbase\app\forms\rules\conditions\DoesNotEndWithCondition;
use barrelstrength\sproutbase\app\forms\rules\conditions\DoesNotStartWithCondition;
use barrelstrength\sproutbase\app\forms\rules\conditions\EndsWithCondition;
use barrelstrength\sproutbase\app\forms\rules\conditions\IsCondition;
use barrelstrength\sproutbase\app\forms\rules\conditions\IsNotCondition;
use barrelstrength\sproutbase\app\forms\rules\conditions\StartsWithCondition;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Template as TemplateHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;


/**
 *
 * @property array $elementValidationRules
 * @property string $svgIconPath
 * @property mixed $settingsHtml
 * @property mixed $exampleInputHtml
 * @property array $compatibleConditions
 * @property array $compatibleCraftFieldTypes
 * @property array $countries
 */
class Phone extends FormField implements PreviewableFieldInterface
{
    /**
     * @var string
     */
    public $cssClasses;

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

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Phone');
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseassets/icons/phone.svg';
    }

    /**
     * @return string|null
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSettingsHtml()
    {
        return SproutBase::$app->phoneField->getSettingsHtml($this);
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
     * @param ElementInterface|null $element
     *
     * @return array|mixed|string|null
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        return SproutBase::$app->phoneField->serializeValue($value);
    }

    /**
     * @inheritdoc
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout/forms/_components/fields/formfields/phone/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @param mixed $value
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
     * @param mixed $value
     * @param Entry $entry
     * @param array|null $renderingOptions
     *
     * @return Markup
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getFrontEndInputHtml($value, Entry $entry, array $renderingOptions = null): Markup
    {
        $name = $this->handle;
        $country = $value['country'] ?? $this->country;
        $countries = SproutBase::$app->phoneField->getCountries();
        $val = $value['phone'] ?? null;

        $rendered = Craft::$app->getView()->renderTemplate('phone/input',
            [
                'name' => $name,
                'value' => $val,
                'field' => $this,
                'entry' => $entry,
                'country' => $country,
                'countries' => $countries,
                'renderingOptions' => $renderingOptions
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        $html = '';

        if ($value->international) {
            $fullNumber = $value->international;
            $html = '<a href="tel:'.$fullNumber.'" target="_blank">'.$fullNumber.'</a>';
        }

        return $html;
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
    public function getCompatibleCraftFieldTypes(): array
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        return [
            'barrelstrength\\sproutfields\\fields\\Phone'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCompatibleConditions()
    {
        return [
            new IsCondition(),
            new IsNotCondition(),
            new ContainsCondition(),
            new DoesNotContainCondition(),
            new StartsWithCondition(),
            new DoesNotStartWithCondition(),
            new EndsWithCondition(),
            new DoesNotEndWithCondition()
        ];
    }
}
