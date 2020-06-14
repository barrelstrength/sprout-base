<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\services;

use barrelstrength\sproutbase\app\fields\models\Phone as PhoneModel;
use barrelstrength\sproutbase\SproutBase;
use CommerceGuys\Addressing\Country\CountryRepository;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\helpers\Json;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Component;
use yii\base\Exception;

/**
 *
 * @property array $countries
 */
class Phone extends Component
{
    /**
     * @param FieldInterface $field
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws Exception
     */
    public function getSettingsHtml(FieldInterface $field): string
    {
        return Craft::$app->getView()->renderTemplate(
            'sprout/fields/_components/fields/formfields/phone/settings',
            [
                'field' => $field,
            ]
        );
    }

    /**
     * @param FieldInterface $field
     * @param                       $value
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws Exception
     */
    public function getInputHtml(FieldInterface $field, $value): string
    {
        /** @var Field $field */
        $name = $field->handle;
        $countryId = Craft::$app->getView()->formatInputId($name.'-country');
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);
        $namespaceCountryId = Craft::$app->getView()->namespaceInputId($countryId);
        $countries = SproutBase::$app->phoneField->getCountries();

        $country = $value['country'] ?? $field->country;
        $val = $value['phone'] ?? null;

        return Craft::$app->getView()->renderTemplate(
            'sprout/fields/_components/fields/formfields/phone/input',
            [
                'namespaceInputId' => $namespaceInputId,
                'namespaceCountryId' => $namespaceCountryId,
                'id' => $inputId,
                'countryId' => $countryId,
                'name' => $field->handle,
                'field' => $field,
                'value' => $val,
                'countries' => $countries,
                'country' => $country
            ]
        );
    }

    /**
     * @param FieldInterface $field
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return array|PhoneModel|string
     */
    public function normalizeValue(FieldInterface $field, $value, ElementInterface $element = null)
    {
        $phoneArray = [];

        if (is_string($value)) {
            $phoneArray = Json::decode($value);
        }

        /** @var Field $field */
        if (is_array($value)) {
            $phoneArray = $value;
        }

        if (isset($phoneArray['phone'], $phoneArray['country'])) {
            $phoneModel = new PhoneModel();
            $phoneModel->country = $phoneArray['country'];
            $phoneModel->phone = $phoneArray['phone'];

            return $phoneModel;
        }

        return $value;
    }

    /**
     * @param $value
     *
     * @return string|null
     */
    public function serializeValue($value)
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof PhoneModel) {
            // Don't save anything unless we can render a phone
            if ($value->getNational() === null) {
                return null;
            }

            return Json::encode([
                'country' => $value->country,
                'phone' => $value->phone
            ]);
        }

        return $value;
    }

    /**
     * Validates a phone number
     *
     * @param $value
     *
     * @return bool
     */
    public function validate($value): bool
    {
        $phone = $value['phone'] ?? null;
        $country = $value['country'] ?? Address::DEFAULT_COUNTRY;

        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $swissNumberProto = $phoneUtil->parse($phone, $country);

            return $phoneUtil->isValidNumber($swissNumberProto);
        } catch (NumberParseException $e) {
            return false;
        }
    }

    /**
     * Return error message
     *
     * @param $field
     * @param $country
     *
     * @return string
     */
    public function getErrorMessage($field, $country = null): string
    {
        // Change empty condition to show default message when toggle settings is unchecked
        if ($field->customPatternErrorMessage) {
            return Craft::t('sprout', $field->customPatternErrorMessage);
        }

        $message = Craft::t('sprout', '{fieldName} is invalid.');

        if (!$country) {
            return $message;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        $exampleNumber = $phoneUtil->getExampleNumber($country);
        $exampleNationalNumber = $phoneUtil->format($exampleNumber, PhoneNumberFormat::NATIONAL);

        return Craft::t('sprout', $message.' Example format: {exampleNumber}', [
            'fieldName' => $field->name,
            'exampleNumber' => $exampleNationalNumber
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
            $countryRepository = new CountryRepository();
            $country = $countryRepository->get($countryCode);

            if ($country) {
                $countries[$countryCode] = $country->getName().' +'.$code;
            }
        }

        asort($countries);

        return $countries;
    }
}
