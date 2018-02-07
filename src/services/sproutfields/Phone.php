<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services\sproutfields;

use barrelstrength\sproutbase\SproutBase;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use yii\base\Component;
use Craft;

/**
 * Class PhoneService
 *
 */
class Phone extends Component
{
    /**
     * @var string
     */
    protected $mask;

    /**
     * @return string
     */
    public function getDefaultMask(): string
    {
        return '###-###-####';
    }

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
     * @param  mixed $field
     *
     * @return string
     */
    public function getErrorMessage($field): string
    {
        // Change empty condition to show default message when toggle settings is unchecked
        if (!empty($field->customPatternErrorMessage)) {
            return Craft::t('sprout-base',$field->customPatternErrorMessage);
        }

        $vars = ['field' => $field->name];

        return Craft::t('sprout-base','{field} is invalid', $vars);
    }

}
