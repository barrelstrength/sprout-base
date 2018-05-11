<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services;

use barrelstrength\sproutbase\app\email\services\Mailers;
use barrelstrength\sproutbase\app\email\services\NotificationEmailEvents;
use barrelstrength\sproutbase\app\email\services\NotificationEmails;
use barrelstrength\sproutbase\app\import\services\Themes;
use barrelstrength\sproutbase\app\reports\services\DataSources;
use barrelstrength\sproutbase\app\reports\services\Exports;
use barrelstrength\sproutbase\app\reports\services\ReportGroups;
use barrelstrength\sproutbase\app\reports\services\Reports;
use barrelstrength\sproutbase\app\fields\services\Utilities;
use barrelstrength\sproutbase\app\fields\services\Url;
use barrelstrength\sproutbase\app\fields\services\Phone;
use barrelstrength\sproutbase\app\fields\services\RegularExpression;
use barrelstrength\sproutbase\app\fields\services\Email;
use barrelstrength\sproutbase\app\fields\services\EmailDropdown;
use barrelstrength\sproutbase\app\fields\services\Address;
use barrelstrength\sproutbase\app\import\services\Importers;
use craft\base\Component;
use barrelstrength\sproutbase\app\email\services\Email as SproutEmail;

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

        $this->template = new Template();
        $this->sproutEmail = new SproutEmail();
    }
}