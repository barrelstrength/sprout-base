<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\sproutfields\web\twig\variables;

use barrelstrength\sproutbase\SproutBase;

class SproutFieldsVariable
{
    /**
     * Return countries for Phone Field
     *
     * @return array
     */
    public function getCountries()
    {
        return SproutBase::$app->phone->getCountries();
    }
}
