<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\models;

use barrelstrength\sproutbase\SproutBase;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\Country\Country;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use Craft;
use craft\base\Model;

/**
 *
 * @property null|string $addressDisplayHtml
 */
class Address extends Model
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $elementId;

    /**
     * @var int
     */
    public $siteId;

    /**
     * @var int
     */
    public $fieldId;

    /**
     * @var string
     */
    public $countryCode = 'US';

    /**
     * @var string
     */
    public $countryThreeLetterCode;

    /**
     * @var string
     */
    public $currencyCode;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var string
     */
    public $administrativeArea;

    /**
     * @var string
     */
    public $administrativeAreaCode;

    /**
     * @var string
     */
    public $locality;

    /**
     * @var string
     */
    public $dependentLocality;

    /**
     * @var string
     */
    public $postalCode;

    /**
     * @var string
     */
    public $sortingCode;

    /**
     * @var string
     */
    public $address1;

    /**
     * @var string
     */
    public $address2;

    /**
     * @var Country
     */
    public $country;

    public function __toString()
    {
        return SproutBase::$app->addressFormatter->getAddressDisplayHtml($this);
    }

    public function init()
    {
        // Initialize country-related information based on the country code
        if ($this->countryCode) {
            $countryRepository = new CountryRepository();
            $country = $countryRepository->get($this->countryCode);

            $this->country = $country->getName();
            $this->countryCode = $country->getCountryCode();
            $this->countryThreeLetterCode = $country->getThreeLetterCode();
            $this->currencyCode = $country->getCurrencyCode();
            $this->locale = $country->getLocale();

            $subdivisionRepository = new SubdivisionRepository();
            $subdivision = $subdivisionRepository->get($this->administrativeAreaCode, [$this->countryCode]);

            if ($subdivision) {
                $this->administrativeArea = $subdivision->getName();
            }
        }
    }

    /**
     * Return the Address HTML for the appropriate region
     *
     * @return string|null
     */
    public function getAddressDisplayHtml()
    {
        if (!$this->id) {
            return null;
        }

        return SproutBase::$app->addressFormatter->getAddressDisplayHtml($this);
    }

    /**
     * @param $attribute
     *
     * @return bool
     */
    public function validatePostalCode($attribute): bool
    {
        $postalCode = $this->{$attribute};

        if ($postalCode === null) {
            return true;
        }

        $addressFormatRepository = new AddressFormatRepository();
        $addressFormat = $addressFormatRepository->get($this->countryCode);

        if ($addressFormat->getPostalCodePattern() !== null) {
            $pattern = $addressFormat->getPostalCodePattern();

            if (preg_match('/^'.$pattern.'$/', $postalCode)) {
                return true;
            }
        }

        $this->addError($attribute, Craft::t('sprout', '{postalName} is not a valid.', [
            'postalName' => ucwords($addressFormat->getPostalCodeType()),
        ]));

        return true;
    }

    /**
     * @return array
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = ['postalCode', 'validatePostalCode'];
        $rules[] = [
            'address1',
            'required',
            'message' => Craft::t('sprout', 'Address 1 field cannot be blank.')
        ];

        return $rules;
    }
}
