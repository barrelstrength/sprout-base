<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services;

use barrelstrength\sproutbase\services\sproutbase\Template;
use barrelstrength\sproutbase\services\sproutemail\Mailers;
use barrelstrength\sproutbase\services\sproutemail\NotificationEmailEvents;
use barrelstrength\sproutbase\services\sproutemail\NotificationEmails;
use barrelstrength\sproutbase\services\sproutimport\Themes;
use barrelstrength\sproutbase\services\sproutreports\DataSources;
use barrelstrength\sproutbase\services\sproutreports\Exports;
use barrelstrength\sproutbase\services\sproutreports\ReportGroups;
use barrelstrength\sproutbase\services\sproutreports\Reports;
use barrelstrength\sproutbase\services\sproutfields\Utilities;
use barrelstrength\sproutbase\services\sproutfields\Url;
use barrelstrength\sproutbase\services\sproutfields\Phone;
use barrelstrength\sproutbase\services\sproutfields\RegularExpression;
use barrelstrength\sproutbase\services\sproutfields\Email;
use barrelstrength\sproutbase\services\sproutfields\EmailDropdown;
use barrelstrength\sproutbase\services\sproutfields\Address;
use barrelstrength\sproutbase\services\sproutbase\Settings;
use barrelstrength\sproutbase\services\sproutbase\Common;
use barrelstrength\sproutbase\services\sproutimport\Importers;
use craft\base\Component;
use barrelstrength\sproutbase\services\sproutemail\Email as SproutEmail;

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
     * @var Url
     */
    public $url;

    /**
     * @var Email
     */
    public $email;

    /**
     * @var EmailDropdown
     */
    public $emailDropdown;

    /**
     * @var RegularExpression
     */
    public $regularExpression;

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
     * @var NotificationEmailEvents
     */
    public $notificationEvents;

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
     * @var Importers
     */
    public $importers;

    /**
     * @var Themes
     */
    public $themes;

    /**
     * @var Common
     */
    public $common;

    /**
     * @var Template
     */
    public $template;

    /**
     * @var SproutEmail
     */
    public $sproutEmail;

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Sprout Base
        $this->settings = new Settings();

        // Sprout Email
        $this->notifications = new NotificationEmails();
        $this->notificationEvents = new NotificationEmailEvents();
        $this->mailers = new Mailers();

        // Sprout Fields
        $this->address = new Address();
        $this->email = new Email();
        $this->emailDropdown = new EmailDropdown();
        $this->phone = new Phone();
        $this->regularExpression = new RegularExpression();
        $this->url = new Url();
        $this->utilities = new Utilities();
        $this->common = Common::Instance();

        // Sprout Reports
        $this->reports = new Reports();
        $this->reportGroups = new ReportGroups();
        $this->dataSources = new DataSources();
        $this->exports = new Exports();

        // Sprout Import
        $this->importers = new Importers();
        $this->themes = new Themes();

        $this->template    = new Template();
        $this->sproutEmail = new SproutEmail();
    }
}