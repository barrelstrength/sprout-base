<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\models;

use Craft;
use craft\base\Model;
use Exception;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class Phone extends Model
{
    /**
     * @var string
     */
    public $phone;

    /**
     * @var string
     */
    public $country;

    /**
     * @var string|null
     */
    protected $code;

    /**
     * @var string|null
     */
    protected $international;

    /**
     * @var string|null
     */
    protected $national;

    /**
     * @var string|null
     */
    protected $E164;

    /**
     * @var string|null
     */
    protected $RFC3966;

    /**
     * @return string
     */
    public function __toString()
    {
        if (!$this->international) {
            $this->populatePhoneDetails();
        }

        return (string)$this->international;
    }

    public function getCode()
    {
        if (!$this->code) {
            $this->populatePhoneDetails();
        }

        return $this->code;
    }

    public function getInternational()
    {
        if (!$this->international) {
            $this->populatePhoneDetails();
        }

        return $this->international;
    }

    public function getNational()
    {
        if (!$this->national) {
            $this->populatePhoneDetails();
        }

        return $this->national;
    }

    public function getE164()
    {
        if (!$this->E164) {
            $this->populatePhoneDetails();
        }

        return $this->E164;
    }

    public function getRFC3966()
    {
        if (!$this->RFC3966) {
            $this->populatePhoneDetails();
        }

        return $this->RFC3966;
    }

    /**
     * Populate the model with specific details based on the phone number and country
     */
    public function populatePhoneDetails()
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $phoneNumber = $phoneUtil->parse($this->phone, $this->country);
            $code = $phoneUtil->getCountryCodeForRegion($this->country);
            $this->code = $code;
            $this->international = $phoneUtil->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);
            $this->national = $phoneUtil->format($phoneNumber, PhoneNumberFormat::NATIONAL);
            $this->E164 = $phoneUtil->format($phoneNumber, PhoneNumberFormat::E164);
            $this->RFC3966 = $phoneUtil->format($phoneNumber, PhoneNumberFormat::RFC3966);
        } catch (Exception $e) {
            // let's continue
            Craft::error('Unable to populate phone field model: '.$e->getMessage(), __METHOD__);
        }
    }
}
