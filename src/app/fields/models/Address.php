<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\models;

use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Intl\Country\Country;
use CommerceGuys\Addressing\Subdivision\Subdivision;
use Craft;
use barrelstrength\sproutbase\app\fields\helpers\AddressHelper;
use craft\base\Model;

/**
 * Class Address
 *
 * @property string $addressDisplayHtml
 * @property string $addressHtml
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
    public $countryCode;

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

    /**
     * Return the Address HTML for the appropriate region
     *
     * @return string
     */
    public function getAddressDisplayHtml(): string
    {
        if (!$this->id) {
            return '';
        }

        $addressHelper = new AddressHelper();

        return $addressHelper->getAddressDisplayHtml($this);
    }

    /**
     * @todo - Add support for Symfony validation library
     *       https://github.com/commerceguys/addressing
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules[] = ['postalCode', 'validatePostalCode'];
        $rules[] = [
            'address1',
            'required',
            'message' => Craft::t('sprout-base', 'Address 1 cannot be blank.')
        ];

        return $rules;
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

        $this->addError($attribute, Craft::t('sprout-base', '{postalName} is not a valid.', [
            'postalName' => ucwords($addressFormat->getPostalCodeType()),
        ]));

        return true;
    }
}
