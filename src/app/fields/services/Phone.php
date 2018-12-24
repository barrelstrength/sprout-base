<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\services;

use barrelstrength\sproutbase\SproutBase;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use CommerceGuys\Intl\Country\CountryRepository;
use yii\base\Component;
use Craft;

/**
 * Class PhoneService
 *
 *
 * @property array $countries
 */
class Phone extends Component
{
    /**
     * Validates a phone number
     *
     * @param $value
     * @param $country
     *
     * @return bool
     */
    public function validate($value, $country = 'US'): bool
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $swissNumberProto = $phoneUtil->parse($value, $country);
            $isValid = $phoneUtil->isValidNumber($swissNumberProto);
        } catch (NumberParseException $e) {
            SproutBase::error($e->getMessage());
            return false;
        }

        if ($isValid) {
            return true;
        }

        return false;
    }

    /**
     * Return error message
     *
     * @param $field
     * @param $country
     *
     * @return string
     */
    public function getErrorMessage($field, $country): string
    {
        // Change empty condition to show default message when toggle settings is unchecked
        if ($field->customPatternErrorMessage) {
            return Craft::t('sprout-base', $field->customPatternErrorMessage);
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        $exampleNumber = $phoneUtil->getExampleNumber($country);
        $national = $phoneUtil->format($exampleNumber, PhoneNumberFormat::NATIONAL);

        return Craft::t('sprout-base', '{field} is invalid. Required format: '.$national, [
            'field' => $field->name,
            'exampleNumber' => $exampleNumber
        ]);
    }

    /**
     * @return array
     */
    public function getCountries(): array
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $regions = $phoneUtil->getSupportedRegions();
        $countries = [];

        foreach ($regions as $countryCode) {
            $code = $phoneUtil->getCountryCodeForRegion($countryCode);
            $countryRepository = new CountryRepository;
            $country = $countryRepository->get($countryCode);

            if ($country) {
                $countries[$countryCode] = $country->getName().' +'.$code;
            }
        }

        asort($countries);

        return $countries;
    }
}
