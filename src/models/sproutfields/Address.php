<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\models\sproutfields;

use Craft;
use barrelstrength\sproutbase\helpers\AddressHelper;
use craft\base\Model;

/**
 * Class Address
 */
class Address extends Model
{
    protected $addressHelper;

    public $id;
    public $modelId;
    public $countryCode;
    public $administrativeArea;
    public $locality;
    public $dependentLocality;
    public $postalCode;
    public $sortingCode;
    public $address1;
    public $address2;

    public function init()
    {
        $this->addressHelper = new AddressHelper();

        parent::init();
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['postalCode', 'validatePostalCode'];
        $rules[] = ['address1', 'required'];

        return $rules;
    }

    /**
     * @todo - this method exists in this class and in the AddressHelper class. Refactor into one method and cleanup.
     *
     * @param $attribute
     */
    public function validatePostalCode($attribute)
    {
        $addressHelper = new AddressHelper();

        $postalCode = $this->{$attribute};

        if ($postalCode == null) {
            return;
        }

        $countryCode = $this->countryCode;

        if (!$addressHelper->validatePostalCode($countryCode, $postalCode)) {
            $postalName = $addressHelper->getPostalName($countryCode);

            $this->addError($attribute, Craft::t('sprout-base','{postalName} is not a valid.', [
                'postalName' => $postalName,
            ]));
        }
    }

    /**
     * Return the Address HTML for the appropriate region
     *
     * @return string
     */
    public function getAddressHtml()
    {
        if (!$this->id) {
            return '';
        }

        $addressHelper = new AddressHelper();

        return $addressHelper->getAddressWithFormat($this);
    }
}
