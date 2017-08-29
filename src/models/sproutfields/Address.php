<?php
namespace barrelstrength\sproutcore\models\sproutfields;

use barrelstrength\sproutcore\SproutCore;
use barrelstrength\sproutcore\helpers\AddressHelper;
use craft\base\Model;

/**
 * Class sproutSeo_AddressInfoModel
 *
 */
class Address extends Model
{
	protected $addressHelper;

	public $id;
	public $modelId;
	public $countryCode;
	public $administrativeArea;
	public $locality;
	public $dependentLocality;
	public $postalCode;
	public $sortingCode;
	public $address1;
	public $address2;

	public $dateCreated;
	public $dateUpdated;
	public $uid;

	public function init()
	{
		$this->addressHelper = new AddressHelper();

		parent::init();
	}

	public function rules()
	{
		$rules = parent::rules();

		$rules[] = ['postalCode', 'validatePostalCode'];
		$rules[] = ['address1', 'required'];

		return $rules;
	}

	public function validatePostalCode($attribute)
	{
		$postalCode = $this->{$attribute};

		if ($postalCode == null) return;

		$countryCode = $this->countryCode;

		if (!$this->addressHelper->validatePostalCode($countryCode, $postalCode))
    {
	    $postalName = $this->addressHelper->getPostalName($countryCode);

	    $params = [
		    'postalName' => $postalName,
	    ];

	    $this->addError($attribute, SproutCore::t("{postalName} is not a valid.", $params));
    }
	}

	/**
	 * Return the Address HTML for the appropriate region
	 *
	 * @return string
	 */
	public function getAddressHtml()
	{
		if (!$this->id)
		{
			return "";
		}

		$address = $this->addressHelper->getAddressWithFormat($this);

		return $address;
	}
}
