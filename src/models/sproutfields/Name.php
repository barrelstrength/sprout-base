<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\models\sproutfields;

use Craft;
use craft\base\Model;

/**
 * Class Name
 */
class Name extends Model
{
    /**
     * @var string
     */
    public $prefix;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $middleName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $suffix;

    /**
     * @return string
     */
    public function getFullNameShort() {

        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return string
     */
    public function getFullName() {

        $fullName = '';

        if ($this->prefix)
        {
            $fullName .= $this->prefix;
        }

        if ($this->prefix)
        {
            $fullName .= $this->firstName;
        }

        if ($this->prefix)
        {
            $fullName .= $this->middleName;
        }

        if ($this->prefix)
        {
            $fullName .= $this->lastName;
        }

        if ($this->prefix)
        {
            $fullName .= $this->suffix;
        }

        return $fullName;
    }
}
