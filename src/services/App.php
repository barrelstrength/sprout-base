<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\services;

use barrelstrength\sproutcore\services\sproutreports\DataSources;
use barrelstrength\sproutcore\services\sproutreports\Exports;
use barrelstrength\sproutcore\services\sproutreports\Reports;
use barrelstrength\sproutcore\services\sproutfields\Utilities;
use barrelstrength\sproutcore\services\sproutfields\Link;
use barrelstrength\sproutcore\services\sproutfields\Phone;
use barrelstrength\sproutcore\services\sproutfields\RegularExpression;
use barrelstrength\sproutcore\services\sproutfields\Email;
use barrelstrength\sproutcore\services\sproutfields\EmailSelect;
use barrelstrength\sproutcore\services\sproutfields\Address;
use barrelstrength\sproutcore\services\sproutcore\Settings;
use craft\base\Component;

class App extends Component
{
	/**
	 * @var Address
	 */
	public $address;

	/**
	 * @var Phone
	 */
	public $phone;

	/**
	 * @var Utilities
	 */
	public $utilities;

	/**
	 * @var Link
	 */
	public $link;

	/**
	 * @var Email
	 */
	public $email;

	/**
	 * @var RegularExpression
	 */
	public $regularExpression;

	/**
	 * @var EmailSelect
	 */
	public $emailSelect;

	/**
	 * @var Reports
	 */
	public $reports;

	/**
	 * @var DataSources
	 */
	public $dataSources;

	/**
	 * @var Exports
	 */
	public $exports;

	/**
	 * @var Settings
	 */
	public $settings;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		// Sprout Core
		$this->settings = new Settings();

		// Sprout Fields
		$this->address = new Address();
		$this->phone = new Phone();
		$this->utilities = new Utilities();
		$this->link = new Link();
		$this->email = new Email();
		$this->regularExpression = new RegularExpression();
		$this->emailSelect = new EmailSelect();

		// Sprout Reports
		$this->reports = new Reports();
		$this->dataSources = new DataSources();
		$this->exports = new Exports();
	}
}