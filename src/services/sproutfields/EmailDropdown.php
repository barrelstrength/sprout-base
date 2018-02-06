<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services\sproutfields;

use yii\base\Component;

class EmailDropdown extends Component
{
    /**
     * @param      $options
     * @param null $value
     *
     * @return mixed
     */
    public function obfuscateEmailAddresses($options, $value = null)
    {
        foreach ($options as $key => $option) {
            $options[$key]['value'] = $key;

            if ($option['value'] == $value) {
                $options[$key]['selected'] = 1;
            } else {
                $options[$key]['selected'] = 0;
            }
        }

        return $options;
    }
}
