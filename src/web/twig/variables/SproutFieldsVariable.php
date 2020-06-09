<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\twig\variables;

use barrelstrength\sproutbase\SproutBase;

class SproutFieldsVariable
{
    /**
     * Return countries for Phone Field
     *
     * @return array
     */
    public function getCountries(): array
    {
        return SproutBase::$app->phoneField->getCountries();
    }
}
