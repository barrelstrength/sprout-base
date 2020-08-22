<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\fields\formfields;

use barrelstrength\sproutbase\app\fields\base\AddressFieldTrait;
use barrelstrength\sproutbase\app\fields\models\Address as AddressModel;
use barrelstrength\sproutbase\app\forms\base\FormField;
use barrelstrength\sproutbase\app\forms\elements\Entry;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\errors\SiteNotFoundException;
use craft\helpers\Template as TemplateHelper;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;

class Address extends FormField implements PreviewableFieldInterface
{
    use AddressFieldTrait;

    /**
     * @var string
     */
    public $cssClasses;

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Address');
    }

    public static function hasContentColumn(): bool
    {
        return false;
    }

    public function hasMultipleLabels(): bool
    {
        return true;
    }

    public function getSvgIconPath(): string
    {
        return '@sproutbaseassets/icons/map-marker-alt.svg';
    }

    /**
     * @return string|null
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws SiteNotFoundException
     */
    public function getSettingsHtml()
    {
        return SproutBase::$app->addressField->getSettingsHtml($this);
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
        return SproutBase::$app->addressField->getInputHtml($this, $value, $element);
    }

    /**
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return AddressModel|mixed|null
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        return SproutBase::$app->addressField->normalizeValue($this, $value, $element);
    }

    /**
     * @param ElementInterface $element
     * @param bool $isNew
     *
     * @throws Throwable
     */
    public function afterElementSave(ElementInterface $element, bool $isNew)
    {
        SproutBase::$app->addressField->afterElementSave($this, $element, $isNew);
        parent::afterElementSave($element, $isNew);
    }

    /**
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout/forms/_components/fields/formfields/address/example',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * @param mixed $value
     * @param Entry $entry
     * @param array|null $renderingOptions
     *
     * @return Markup
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getFrontEndInputHtml($value, Entry $entry, array $renderingOptions = null): Markup
    {
        $name = $this->handle;
        $settings = $this->getSettings();

        $countryCode = $settings['defaultCountry'] ?? $this->defaultCountry;
        $showCountryDropdown = $settings['showCountryDropdown'] ?? 0;

        $addressModel = new AddressModel();

        // This defaults to Sprout Base and we need it to get updated to look
        // in the Sprout Forms Form Template location like other fields.
        SproutBase::$app->addressFormatter->setBaseAddressFieldPath('');

        SproutBase::$app->addressFormatter->setNamespace($name);

        if (isset($this->highlightCountries) && count($this->highlightCountries)) {
            SproutBase::$app->addressFormatter->setHighlightCountries($this->highlightCountries);
        }

        SproutBase::$app->addressFormatter->setCountryCode($countryCode);
        SproutBase::$app->addressFormatter->setAddressModel($addressModel);
        SproutBase::$app->addressFormatter->setLanguage($this->defaultLanguage);

        if (count($this->highlightCountries)) {
            SproutBase::$app->addressFormatter->setHighlightCountries($this->highlightCountries);
        }

        $countryInputHtml = SproutBase::$app->addressFormatter->getCountryInputHtml($showCountryDropdown);
        $addressFormHtml = SproutBase::$app->addressFormatter->getAddressFormHtml();

        $rendered = Craft::$app->getView()->renderTemplate('address/input', [
                'field' => $this,
                'entry' => $entry,
                'name' => $this->handle,
                'renderingOptions' => $renderingOptions,
                'addressFormHtml' => TemplateHelper::raw($addressFormHtml),
                'countryInputHtml' => TemplateHelper::raw($countryInputHtml),
                'showCountryDropdown' => $showCountryDropdown,
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    /**
     * @return array
     */
    public function getElementValidationRules(): array
    {
        return ['validateAddress'];
    }

    /**
     * @param Element|ElementInterface $element
     *
     * @return bool
     */
    public function validateAddress(ElementInterface $element): bool
    {
        if (!$this->required) {
            return true;
        }

        $values = $element->getFieldValue($this->handle);

        $addressModel = new AddressModel($values);
        $addressModel->validate();

        if ($addressModel->hasErrors()) {
            $errors = $addressModel->getErrors();

            if ($errors) {
                foreach ($errors as $error) {
                    $firstMessage = $error[0] ?? null;
                    $element->addError($this->handle, $firstMessage);
                }
            }
        }

        return true;
    }

    public function getCompatibleCraftFieldTypes(): array
    {
        return [
            'barrelstrength\\sproutfields\\fields\\Address',
        ];
    }
}
