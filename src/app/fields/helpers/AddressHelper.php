<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\helpers;

use barrelstrength\sproutbase\app\fields\models\Address as AddressModel;
use Craft;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Formatter\DefaultFormatter;
use CommerceGuys\Addressing\Address;

class AddressHelper
{
    /**
     * @var AddressFormatRepository
     */
    protected $addressFormatRepository;

    /**
     * @var SubdivisionRepository
     */
    protected $subdivisionRepository;

    /**
     * Namespace is set dynamically to the field handle of the Address Field
     * being generated.
     *
     * Defaults to 'address' for use in plugins like Sprout SEO.
     *
     * @var
     */
    protected $namespace = 'address';

    /**
     * @var
     */
    protected $addressModel;

    /**
     * @var
     */
    protected $countryCode;

    /**
     * @var
     */
    protected $language = 'en';

    /**
     * @var array 
     */
    protected $highlightCountries = [];

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function setHighlightCountries($highlightCountries)
    {
        $highlightCountries = $this->getHighlightCountries($highlightCountries);

        $this->highlightCountries = $highlightCountries;
    }

    /**
     * Format common countries setting values with country names
     * @param $highlightCountries
     *
     * @return array
     */
    public function getHighlightCountries($highlightCountries)
    {
        $countryRepository = new \CommerceGuys\Intl\Country\CountryRepository();
        $options = [];

        $commonCountries = $highlightCountries;

        if (is_array($commonCountries) && count($commonCountries)) {
            foreach ($commonCountries as $code) {
                $options[$code] = $countryRepository->get($code)->getName();
            }
        }

        return $options;
    }

    /**
     * Our base address field path defaults to the path we use for rendering the Address Field in the
     * Control Panel. In the case of Sprout Forms, we need to override this and set this to blank because
     * Sprout Forms dynamically determines the path so that users can control template overrides.
     *
     * @var string
     */
    private $baseAddressFieldPath = 'sprout-base-fields/_components/fields/formfields/';

    /**
     * @return string
     */
    public function defaultCountryCode(): string
    {
        return 'US';
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param mixed $name
     */
    public function setNamespace($name)
    {
        $this->namespace = $name;
    }

    /**
     * @return mixed
     */
    public function getAddressModel()
    {
        return $this->addressModel;
    }

    /**
     * @param mixed $addressModel
     */
    public function setAddressModel(AddressModel $addressModel = null)
    {
        $this->addressModel = $addressModel ?? new AddressModel();
    }

    /**
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param mixed $countryCode
     */
    public function setCountryCode($countryCode = null)
    {
        $this->countryCode = $countryCode ?? $this->defaultCountryCode();
    }

    /**
     * @return mixed
     */
    public function getBaseAddressFieldPath()
    {
        return $this->baseAddressFieldPath;
    }

    /**
     * @param mixed $baseAddressFieldPath
     */
    public function setBaseAddressFieldPath($baseAddressFieldPath)
    {
        $this->baseAddressFieldPath = $baseAddressFieldPath;
    }

    /**
     * Returns a formatted address to display
     *
     * @param AddressModel $model
     *
     * @return string
     */
    public function getAddressDisplayHtml(AddressModel $model): string
    {
        $address = new Address();
        $addressFormatRepository = new AddressFormatRepository();
        $countryRepository = new CountryRepository();
        $subdivisionRepository = new SubdivisionRepository();

        $formatter = new DefaultFormatter($addressFormatRepository, $countryRepository, $subdivisionRepository);

        $address = $address
            ->withCountryCode($model->countryCode)
            ->withAdministrativeArea($model->administrativeAreaCode)
            ->withLocality($model->locality)
            ->withPostalCode($model->postalCode)
            ->withAddressLine1($model->address1)
            ->withAddressLine2($model->address2);

        if ($model->dependentLocality != null) {
            $address->withDependentLocality($model->dependentLocality);
        }

        return $formatter->format($address);
    }

    /**
     * Returns all input fields necessary for a user submit an address
     *
     * @return null|string|string[]
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getAddressFormHtml()
    {
        $this->subdivisionRepository = new SubdivisionRepository();
        $addressFormatRepository = new AddressFormatRepository();
        $this->addressFormatRepository = $addressFormatRepository->get($this->countryCode);

        $addressLayout = $this->addressFormatRepository->getFormat();

        // Remove unused attributes
        $addressLayout = preg_replace('/%recipient/', '', $addressLayout);
        $addressLayout = preg_replace('/%organization/', '', $addressLayout);
        $addressLayout = preg_replace('/%givenName/', '', $addressLayout);
        $addressLayout = preg_replace('/%familyName/', '', $addressLayout);

        $countryRepository = new CountryRepository();
        $countries = $countryRepository->getList($this->language);

        $countryName = $countries[$this->countryCode];
        if ($countryName) {
            $addressLayout = preg_replace('/'.$countryName.'/', '', $addressLayout);
        }

        // Remove dash on format
        $addressLayout = str_replace('-', '', $addressLayout);

        // Insert line break based on the format
        //$format = nl2br($format);

        // An exception when building our Form Input Field in the CP
        // Removes a backslash character that is needed for the Address Display Only
        if ($this->countryCode === 'TR') {
            $addressLayout = preg_replace('`%locality\/`', '%locality', $addressLayout);
        }

        // More whitespace
        $addressLayout = preg_replace('/,/', '', $addressLayout);

        $addressLayout = preg_replace('/%addressLine1/', $this->getAddressLineInputHtml('address1'), $addressLayout);
        $addressLayout = preg_replace('/%addressLine2/', $this->getAddressLineInputHtml('address2'), $addressLayout);
        $addressLayout = preg_replace('/%dependentLocality/', $this->getDependentLocalityInputHtml(), $addressLayout);
        $addressLayout = preg_replace('/%locality/', $this->getLocalityInputHtml(), $addressLayout);
        $addressLayout = preg_replace('/%administrativeArea/', $this->getAdministrativeAreaInputHtml(), $addressLayout);
        $addressLayout = preg_replace('/%postalCode/', $this->getPostalCodeInputHtml(), $addressLayout);

        if (preg_match('/%sortingCode/', $addressLayout)) {
            $addressLayout = preg_replace('/%sortingCode/', $this->getSortingCodeInputHtml(), $addressLayout);
        }

        if ($this->addressModel->id !== null) {
            $addressLayout .= $this->getAddressIdInputHtml();
        }

        // A few exceptions when building our Form Input Fields for the CP
        // Removes a hardcoded locality that is needed for the Address Display Only
        // These are added automatically in the AddressFormat for front-end display
        if ($this->countryCode === 'AX') {
            $addressLayout = str_replace('ÅLAND', '', $addressLayout);
        }
        if ($this->countryCode === 'JE') {
            $addressLayout = str_replace('JERSEY', '', $addressLayout);
        }

        return $addressLayout;
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    private function getAddressIdInputHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            $this->getBaseAddressFieldPath().'address/_components/hidden', [
            'class' => 'sprout-address-id',
            'name' => $this->namespace,
            'inputName' => 'id',
            'value' => $this->addressModel->id
        ]);
    }

    /**
     * @param bool $showCountryDropdown
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getCountryInputHtml($showCountryDropdown = true): string
    {
        $countryRepository = new CountryRepository();
        $countries = $countryRepository->getList($this->language);

        return Craft::$app->view->renderTemplate(
            $this->getBaseAddressFieldPath().'address/_components/select-country', [
                'fieldClass' => 'sprout-address-country-select',
                'label' => $this->renderAddressLabel('Country'),
                'name' => $this->namespace,
                'inputName' => 'countryCode',
                'autocomplete' => 'country',
                'options' => $countries,
                'value' => $this->countryCode ?? $this->defaultCountryCode(),
                'hideDropdown' => !$showCountryDropdown,
                'highlightCountries' => $this->highlightCountries
            ]
        );
    }

    /**
     * @param $addressName
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    private function getAddressLineInputHtml($addressName): string
    {
        $value = $this->addressModel->{$addressName};

        $label = $this->renderAddressLabel('Address 1');
        $autocomplete = 'address-line1';

        if ($addressName === 'address2') {
            $label = $this->renderAddressLabel('Address 2');
            $autocomplete = 'address-line2';
        }

        return Craft::$app->view->renderTemplate(
            $this->getBaseAddressFieldPath().'address/_components/text', [
                'fieldClass' => 'sprout-address-onchange-country',
                'label' => $label,
                'name' => $this->namespace,
                'inputName' => $addressName,
                'autocomplete' => $autocomplete,
                'value' => $value
            ]
        );
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    private function getSortingCodeInputHtml(): string
    {
        $value = $this->addressModel->sortingCode;

        return Craft::$app->view->renderTemplate(
            $this->getBaseAddressFieldPath().'address/_components/text', [
                'fieldClass' => 'sprout-address-onchange-country',
                'label' => $this->renderAddressLabel('Sorting Code'),
                'name' => $this->namespace,
                'inputName' => 'sortingCode',
                'autocomplete' => 'address-level4',
                'value' => $value
            ]
        );
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    private function getLocalityInputHtml(): string
    {
        $value = $this->addressModel->locality;

        return Craft::$app->view->renderTemplate(
            $this->getBaseAddressFieldPath().'address/_components/text', [
                'fieldClass' => 'sprout-address-onchange-country',
                'label' => $this->renderAddressLabel($this->addressFormatRepository->getLocalityType()),
                'name' => $this->namespace,
                'inputName' => 'locality',
                'autocomplete' => 'address-level2',
                'value' => $value
            ]
        );
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    private function getDependentLocalityInputHtml(): string
    {
        $value = $this->addressModel->dependentLocality;

        return Craft::$app->view->renderTemplate(
            $this->getBaseAddressFieldPath().'address/_components/text', [
                'fieldClass' => 'sprout-address-onchange-country',
                'label' => $this->renderAddressLabel($this->addressFormatRepository->getDependentLocalityType()),
                'name' => $this->namespace,
                'inputName' => 'dependentLocality',
                'autocomplete' => 'address-level3',
                'value' => $value
            ]
        );
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    private function getAdministrativeAreaInputHtml(): string
    {
        $value = $this->addressModel->administrativeAreaCode;

        $states = $this->subdivisionRepository->getList([$this->countryCode], $this->language);

        if ($states && !empty($states)) {
            return Craft::$app->view->renderTemplate(
                $this->getBaseAddressFieldPath().'address/_components/select', [
                    'fieldClass' => 'sprout-address-onchange-country',
                    'label' => $this->renderAddressLabel($this->addressFormatRepository->getAdministrativeAreaType()),
                    'name' => $this->namespace,
                    'inputName' => 'administrativeAreaCode',
                    'autocomplete' => 'address-level1',
                    'options' => $states,
                    'value' => $value
                ]
            );
        }

        return Craft::$app->view->renderTemplate(
            $this->getBaseAddressFieldPath().'address/_components/text', [
                'fieldClass' => 'sprout-address-onchange-country',
                'label' => $this->renderAddressLabel($this->addressFormatRepository->getAdministrativeAreaType()),
                'name' => $this->namespace,
                'inputName' => 'administrativeAreaCode',
                'autocomplete' => 'address-level1',
                'value' => $value
            ]
        );
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getPostalCodeInputHtml(): string
    {
        $value = $this->addressModel->postalCode;

        return Craft::$app->view->renderTemplate(
            $this->getBaseAddressFieldPath().'address/_components/text', [
                'fieldClass' => 'sprout-address-onchange-country',
                'label' => $this->renderAddressLabel($this->addressFormatRepository->getPostalCodeType()),
                'name' => $this->namespace,
                'inputName' => 'postalCode',
                'autocomplete' => 'postal-code',
                'value' => $value
            ]
        );
    }

    /**
     * @param $label
     *
     * @return null|string
     */
    protected function renderAddressLabel($label)
    {
        return Craft::t('sprout-base', str_replace('_', ' ', ucwords($label)));
    }
}