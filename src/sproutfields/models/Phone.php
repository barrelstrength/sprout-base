<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\sproutfields\models;

use craft\base\Model;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Class Name
 */
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
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $international;

    /**
     * @var string
     */
    public $national;

    /**
     * @var string
     */
    public $E164;

    /**
     * @var string
     */
    public $RFC3966;

    /**
     * @var PhoneNumberUtil
     */
    private $phoneUtil;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->international ?? '';
    }

    /**
     * Phone constructor.
     *
     * @param string $phone
     * @param string $country
     */
    public function __construct($phone, $country)
    {
        $this->phoneUtil = PhoneNumberUtil::getInstance();
        $this->phone = $phone;
        $this->country = $country;

        try {
            $phoneNumber = $this->phoneUtil->parse(
                $phone,
                $country
            );
            $code = $this->phoneUtil->getCountryCodeForRegion($country);
            $this->code = $code;
            $this->international = $this->phoneUtil->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);
            $this->national = $this->phoneUtil->format($phoneNumber, PhoneNumberFormat::NATIONAL);
            $this->E164 = $this->phoneUtil->format($phoneNumber, PhoneNumberFormat::E164);
            $this->RFC3966 = $this->phoneUtil->format($phoneNumber, PhoneNumberFormat::RFC3966);
        } catch (\Exception $e) {
            // let's continue
        }
    }

    public function getAsJson(): string
    {
        return json_encode([
            'country' => $this->country,
            'phone' => $this->phone
        ]);
    }
}
