<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services\sproutfields;

use yii\base\Component;

use barrelstrength\sproutbase\SproutBase;

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
        return "###-###-####";
    }

    /**
     * Validates a phone number against a given mask/pattern
     *
     * @param $value
     * @param $mask
     *
     * @return bool
     */
    public function validate($value, $mask): bool
    {
        if ($value == $mask) {
            return true;
        }

        $mask = preg_quote($mask);

        $phonePattern = $this->convertMaskToRegEx($mask);

        if ($value == $mask || preg_match($phonePattern, $value)) {
            return true;
        }

        return false;
    }

    /**
     * Converts a pattern mask into a regular expression
     *
     * @param $mask
     *
     * @return string
     */
    public function convertMaskToRegEx($mask): string
    {
        $this->mask = $mask;

        $hashPatterns = $this->prepareHashesForRegEx();

        return $this->getRegEx($hashPatterns);
    }

    /**
     * Prepare mask info to be converted to regular expressions
     *
     * @return array
     */
    protected function prepareHashesForRegEx(): array
    {
        $mask = $this->mask;

        // Explode hashes
        preg_match_all("/#*/", $mask, $matches);

        // Remove empty array values
        $hashes = array_values(array_filter($matches[0]));

        $hashPatterns = [];

        // Organize for regex replacement
        if ($hashes) {
            $i = 0;


            foreach ($hashes as $hash) {
                $length = strlen($hash);

                $hashPatterns[$i]['pattern'] = "([0-9]{".$length."})";
                $hashPatterns[$i]['hash'] = $hash;
                $hashPatterns[$i]['preg_replace'] = "([#]{".$length."})";

                $i++;
            }
        }

        return $hashPatterns;
    }

    /**
     * Processes mask info and returns a regular expression
     *
     * @param $hashPatterns
     *
     * @return string
     */
    protected function getRegEx($hashPatterns): string
    {
        $mask = $this->mask;

        $regEx = '';

        if ($hashPatterns) {
            foreach ($hashPatterns as $hashPattern) {
                $pattern = $hashPattern['pattern'];
                $preg_replace = $hashPattern['preg_replace'];

                // Add fourth parameter for non-greedy matching
                $mask = preg_replace($preg_replace, $pattern, $mask, 1);
            }

            $regEx = '/^'.$mask.'$/';
        }

        return $regEx;
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
            return SproutBase::t($field->customPatternErrorMessage);
        }

        $vars = ['field' => $field->name, 'format' => $field->mask];

        return SproutBase::t('{field} is invalid. Required format: {format}', $vars);
    }

}
