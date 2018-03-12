<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\models\sproutfields;

use craft\base\Model;

/**
 * Class Name
 */
class Name extends Model
{
    /**
     * @var string
     */
    public $fullName;

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
    public function __toString()
    {
        $name = '';

        if ($this->getFullName()) {
            $name = $this->getFullName();
        }

        return $name;
    }

    /**
     * @return string
     */
    public function getFriendlyName()
    {

        return trim($this->firstName);
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        $firstName = trim($this->firstName);
        $lastName = trim($this->lastName);

        if (!$firstName && !$lastName) {
            return null;
        }

        $name = $firstName;

        if ($firstName && $lastName) {
            $name .= ' ';
        }

        $name .= $lastName;

        return $name ?? '';
    }

    /**
     * @return string
     */
    public function getFullNameExtended()
    {

        $this->fullName = '';

        $this->addName($this->prefix);
        $this->addName($this->firstName);
        $this->addName($this->middleName);
        $this->addName($this->lastName);
        $this->addName($this->suffix);

        $this->fullName = trim($this->fullName);

        return $this->fullName;
    }

    /**
     * @param $name
     */
    protected function addName($name)
    {
        if ($name) {
            $this->fullName .= ' '.$name;
        }
    }
}
