<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\models;

use craft\base\Model;

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
    public function getFriendlyName(): string
    {

        return trim($this->firstName);
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        $firstName = trim($this->firstName);
        $lastName = trim($this->lastName);

        if (!$firstName && !$lastName) {
            return '';
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
    public function getFullNameExtended(): string
    {
        $fullName = '';

        $fullName .= $this->appendName($this->prefix);
        $fullName .= $this->appendName($this->firstName);
        $fullName .= $this->appendName($this->middleName);
        $fullName .= $this->appendName($this->lastName);
        $fullName .= $this->appendName($this->suffix);

        return trim($fullName);
    }

    /**
     * @param $name
     *
     * @return string|null
     */
    protected function appendName($name)
    {
        if ($name) {
            return ' '.$name;
        }

        return null;
    }
}
