<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\base;

trait AddressFieldTrait
{
    /**
     * @var string
     */
    public $defaultLanguage = 'en';

    /**
     * @var string
     */
    public $defaultCountry = 'US';

    /**
     * @var bool
     */
    public $showCountryDropdown = true;

    /**
     * @var array
     */
    public $highlightCountries = [];

    /**
     * This will be populated with the addressId if it should be removed from the database
     *
     * @var int
     */
    protected $_deletedAddressId;

    /**
     * @return int|null
     */
    public function getDeletedAddressId()
    {
        return $this->_deletedAddressId;
    }

    /**
     * @param int $addressId
     */
    public function setDeletedAddressId($addressId)
    {
        $this->_deletedAddressId = $addressId;
    }
}