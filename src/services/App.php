<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services;

use barrelstrength\sproutbase\services\sproutemail\Mailers;
use barrelstrength\sproutbase\services\sproutemail\NotificationEmails;
use barrelstrength\sproutbase\services\sproutreports\DataSources;
use barrelstrength\sproutbase\services\sproutreports\Exports;
use barrelstrength\sproutbase\services\sproutreports\ReportGroups;
use barrelstrength\sproutbase\services\sproutreports\Reports;
use barrelstrength\sproutbase\services\sproutfields\Utilities;
use barrelstrength\sproutbase\services\sproutfields\Link;
use barrelstrength\sproutbase\services\sproutfields\Phone;
use barrelstrength\sproutbase\services\sproutfields\RegularExpression;
use barrelstrength\sproutbase\services\sproutfields\Email;
use barrelstrength\sproutbase\services\sproutfields\EmailSelect;
use barrelstrength\sproutbase\services\sproutfields\Address;
use barrelstrength\sproutbase\services\sproutbase\Settings;
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
     * @var ReportGroups
     */
    public $reportGroups;

    /**
     * @var NotificationEmails
     */
    public $notifications;

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
     * @var Mailers
     */
    public $mailers;

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
        $this->reportGroups = new ReportGroups();
        $this->dataSources = new DataSources();
        $this->exports = new Exports();

        // Notifications
        $this->notifications = new NotificationEmails();
        $this->mailers = new Mailers();
    }
}