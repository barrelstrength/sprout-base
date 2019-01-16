<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\base;

use barrelstrength\sproutbase\app\fields\helpers\AddressHelper;
use barrelstrength\sproutbase\app\fields\services\Address;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use CommerceGuys\Intl\Language\Language;
use CommerceGuys\Intl\Language\LanguageRepository;
use craft\base\Element;
use craft\base\ElementInterface;
use Craft;
use barrelstrength\sproutbase\SproutBase;
use craft\base\Field;
use barrelstrength\sproutbase\app\fields\models\Address as AddressModel;
use CommerceGuys\Intl\Country\CountryRepository;
use craft\helpers\Template;

/**
 * Trait AddressFieldTrait
 *
 * @package barrelstrength\sproutbase\app\fields\base
 *
 * @property null|string $settingsHtml
 */
trait AddressFieldTrait
{
    /**
     * @var AddressHelper $addressHelper
     */
    public $addressHelper;

    /**
     * @var string
     */
    public $defaultLanguage;

    /**
     * @var string
     */
    public $defaultCountry;

    /**
     * @var bool
     */
    public $showCountryDropdown = true;

    /**
     * @deprecated No longer in user. Necessary in craft 3.1 migration
     */
    public $hideCountryDropdown;

    /**
     * @var array
     */
    public $highlightCountries = [];

    /**
     * AddressFieldTrait constructor.
     */
    public function init()
    {
        $this->addressHelper = new AddressHelper();
    }

    /**
     * @return null|string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml()
    {
        $allCraftLocaleIds = Craft::$app->getI18n()->getAllLocaleIds();

        // Reads the language definitions from resources/language.
        $languageRepository = new LanguageRepository();
        $supportedLanguages = $languageRepository->getAll();

        $availableLanguages = [];
        foreach ($allCraftLocaleIds as $craftLocaleId) {
            if (isset($supportedLanguages[$craftLocaleId])) {
                /**
                 * @var Language $language
                 */
                $language = $supportedLanguages[$craftLocaleId];
                $availableLanguages[$craftLocaleId] = $language->getName();
            }
        }

        if ($this->defaultLanguage === null) {
            $this->defaultLanguage = Address::DEFAULT_LANGUAGE;

            // If the primary site language is available choose it as a default language.
            $primarySiteLocaleId = Craft::$app->getSites()->getPrimarySite()->language;
            if (isset($availableLanguages[$primarySiteLocaleId])) {
                $this->defaultLanguage = $primarySiteLocaleId;
            }
        }

        // Countries
        if ($this->defaultCountry === null) {
            $this->defaultCountry = Address::DEFAULT_COUNTRY;
        }

        $countryRepository = new CountryRepository();
        $countries = $countryRepository->getList($this->defaultLanguage);

        if (count($this->highlightCountries)) {
            $highlightCountries = $this->addressHelper->getHighlightCountries($this->highlightCountries);
            $countries = array_merge($highlightCountries, $countries);
        }

        return Craft::$app->getView()->renderTemplate(
            'sprout-base-fields/_components/fields/formfields/address/settings', [
                'field' => $this,
                'countries' => $countries,
                'languages' => $availableLanguages
            ]
        );
    }

    /**
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getInputHtml(
        $value, /** @noinspection PhpUnusedParameterInspection */
        ElementInterface $element = null
    ): string {
        /** @var $this Field */
        $name = $this->handle;

        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputName = Craft::$app->getView()->namespaceInputName($inputId);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        /** @var $this Field */
        $settings = $this->getSettings();

        $defaultCountryCode = $settings['defaultCountry'] ?? null;
        $showCountryDropdown = $settings['showCountryDropdown'] ?? null;

        $addressId = null;

        if (is_object($value)) {
            $addressId = $value->id;
        } elseif (is_array($value)) {
            $addressId = $value['id'];
        }

        $addressModel = SproutBase::$app->addressField->getAddressById($addressId);

        $countryCode = $addressModel->countryCode ?? $defaultCountryCode;

        $this->addressHelper->setNamespace($name);
        $this->addressHelper->setCountryCode($countryCode);
        $this->addressHelper->setAddressModel($addressModel);

        if (count($this->highlightCountries)) {
            $this->addressHelper->setHighlightCountries($this->highlightCountries);
        }

        $addressDisplayHtml = $addressId ? $this->addressHelper->getAddressDisplayHtml($addressModel) : '';
        $countryInputHtml = $this->addressHelper->getCountryInputHtml($showCountryDropdown);
        $addressFormHtml = $this->addressHelper->getAddressFormHtml();

        return Craft::$app->getView()->renderTemplate(
            'sprout-base-fields/_components/fields/formfields/address/input', [
                'namespaceInputId' => $namespaceInputId,
                'namespaceInputName' => $namespaceInputName,
                'field' => $this,
                'fieldId' => $addressModel->fieldId ?? null,
                'addressId' => $addressId,
                'defaultCountryCode' => $defaultCountryCode,
                'addressDisplayHtml' => Template::raw($addressDisplayHtml),
                'countryInputHtml' => Template::raw($countryInputHtml),
                'addressFormHtml' => Template::raw($addressFormHtml),
                'showCountryDropdown' => $showCountryDropdown
            ]
        );
    }

    /**
     * Prepare our Address for use as an AddressModel
     *
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return array|AddressModel|int|mixed|string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function normalizeValue(
        $value, /** @noinspection PhpUnusedParameterInspection */
        ElementInterface $element = null
    ) {
        $addressModel = new AddressModel();

        // Numeric value when retrieved from db
        if (is_numeric($value)) {
            $addressModel = SproutBase::$app->addressField->getAddressById($value);
        }

        // Array value from post data
        if (is_array($value)) {

            if (!empty($value['delete'])) {
                SproutBase::$app->addressField->deleteAddressById($value['id']);
            } else {
                $value['fieldId'] = $this->id ?? null;
                $addressModel = new AddressModel();
                $addressModel->setAttributes($value, false);
            }
        }

        // Adds country property that return country name
        if ($addressModel->countryCode) {
            $countryRepository = new CountryRepository();
            $country = $countryRepository->get($addressModel->countryCode);

            $addressModel->country = $country->getName();
            $addressModel->countryCode = $country->getCountryCode();
            $addressModel->countryThreeLetterCode = $country->getThreeLetterCode();
            $addressModel->currencyCode = $country->getCurrencyCode();
            $addressModel->locale = $country->getLocale();

            $subdivisionRepository = new SubdivisionRepository();
            $subdivision = $subdivisionRepository->get($addressModel->administrativeAreaCode, [$addressModel->countryCode]);

            if ($subdivision) {
                $addressModel->administrativeArea = $subdivision->getName();
            }
        }

        // return null when clearing address to save null value on content table
        if (!$addressModel->validate(null, false)) {
            return $value;
        }

        return $addressModel;
    }

    /**
     *
     * Prepare the field value for the database.
     *
     * We store the Address ID in the content column.
     *
     * @param mixed                 $value
     * @param ElementInterface|null $element
     *
     * @return array|bool|mixed|null|string
     */
    public function serializeValue(
        $value, /** @noinspection PhpUnusedParameterInspection */
        ElementInterface $element = null
    ) {
        if (empty($value)) {
            return false;
        }

        $addressId = null;

        // When loading a Field Layout with an Address Field
        if (is_object($value) && get_class($value) == AddressModel::class) {
            $addressId = $value->id;
        }

        // For the ResaveElements task $value is the id
        if (is_int($value)) {
            $addressId = $value;
        }

        // When the field is saved by post request the id an attribute on $value
        if (isset($value['id']) && $value['id']) {
            $addressId = $value['id'];
        }

        // Save the addressId in the content table
        return $addressId;
    }

    /**
     * Save our Address Field a first time and assign the Address Record ID back to the Address field model
     * We'll save our Address Field a second time in afterElementSave to capture the Element ID for new entries.
     *
     * @param Element|ElementInterface $element
     * @param bool                     $isNew
     *
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function beforeElementSave(
        ElementInterface $element, /** @noinspection PhpUnusedParameterInspection */
        bool $isNew
    ): bool {
        $address = $element->getFieldValue($this->handle);

        if ($address instanceof AddressModel) {
            $address->elementId = $element->id;
            $address->siteId = $element->siteId;
            $address->fieldId = $this->id;

            SproutBase::$app->addressField->saveAddress($address);
        }

        return true;
    }

    /**
     * Save our Address Field a second time for New Entries to ensure we have the Element ID.
     *
     * @param Element|ElementInterface $element
     * @param bool                     $isNew
     *
     * @return bool|void
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function afterElementSave(ElementInterface $element, bool $isNew)
    {
        if (!$isNew) {
            return;
        }

        /** @var $this Field */
        $address = $element->getFieldValue($this->handle);

        if ($address instanceof AddressModel) {
            $address->elementId = $element->id;
            SproutBase::$app->addressField->saveAddress($address);
        }
    }
}