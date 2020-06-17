<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\services;

use barrelstrength\sproutbase\app\campaigns\services\CampaignEmails;
use barrelstrength\sproutbase\app\campaigns\services\CampaignTypes;
use barrelstrength\sproutbase\app\email\services\EmailTemplates;
use barrelstrength\sproutbase\app\email\services\Mailers;
use barrelstrength\sproutbase\app\email\services\NotificationEmailEvents;
use barrelstrength\sproutbase\app\email\services\NotificationEmails;
use barrelstrength\sproutbase\app\fields\services\Address;
use barrelstrength\sproutbase\app\fields\services\AddressFormatter;
use barrelstrength\sproutbase\app\fields\services\Email;
use barrelstrength\sproutbase\app\fields\services\EmailDropdown;
use barrelstrength\sproutbase\app\fields\services\Name;
use barrelstrength\sproutbase\app\fields\services\Phone;
use barrelstrength\sproutbase\app\fields\services\RegularExpression;
use barrelstrength\sproutbase\app\fields\services\Url;
use barrelstrength\sproutbase\app\fields\services\Utilities as FieldUtilities;
use barrelstrength\sproutbase\app\forms\services\FormEntries;
use barrelstrength\sproutbase\app\forms\services\EntryStatuses;
use barrelstrength\sproutbase\app\forms\services\FormFields;
use barrelstrength\sproutbase\app\forms\services\Forms;
use barrelstrength\sproutbase\app\forms\services\FrontEndFields;
use barrelstrength\sproutbase\app\forms\services\FormGroups;
use barrelstrength\sproutbase\app\forms\services\Integrations;
use barrelstrength\sproutbase\app\forms\services\Rules;
use barrelstrength\sproutbase\app\metadata\services\ElementMetadata;
use barrelstrength\sproutbase\app\metadata\services\GlobalMetadata;
use barrelstrength\sproutbase\app\metadata\services\Optimize;
use barrelstrength\sproutbase\app\metadata\services\Schema;
use barrelstrength\sproutbase\app\redirects\services\Redirects;
use barrelstrength\sproutbase\app\reports\services\DataSources;
use barrelstrength\sproutbase\app\reports\services\Exports;
use barrelstrength\sproutbase\app\reports\services\ReportGroups;
use barrelstrength\sproutbase\app\reports\services\Reports;
use barrelstrength\sproutbase\app\reports\services\TwigDataSource;
use barrelstrength\sproutbase\app\reports\services\Visualizations;
use barrelstrength\sproutbase\app\sentemail\services\SentEmails;
use barrelstrength\sproutbase\app\sitemaps\services\Sitemaps;
use barrelstrength\sproutbase\app\sitemaps\services\XmlSitemap;
use barrelstrength\sproutbase\app\uris\services\UrlEnabledSections;
use craft\base\Component;

class App extends Component
{
    /**
     * @var FormGroups
     */
    public $formGroups;

    /**
     * @var Forms
     */
    public $forms;

    /**
     * @var FormFields
     */
    public $formFields;

    /**
     * @var FormEntries
     */
    public $formEntries;

    /**
     * @var EntryStatuses
     */
    public $entryStatuses;

    /**
     * @var FrontEndFields
     */
    public $frontEndFields;

    /**
     * @var Integrations
     */
    public $integrations;

    /**
     * @var Rules
     */
    public $rules;

    /**
     * @var Optimize
     */
    public $optimize;

    /**
     * @var GlobalMetadata
     */
    public $globalMetadata;

    /**
     * @var ElementMetadata
     */
    public $elementMetadata;

    /**
     * @var Schema
     */
    public $schema;

    /**
     * @var CampaignEmails
     */
    public $campaignEmails;

    /**
     * @var CampaignTypes
     */
    public $campaignTypes;

    /**
     * @var DataSources
     */
    public $dataSources;

    /**
     * @var TwigDataSource
     */
    public $twigDataSource;

    /**
     * @var Exports
     */
    public $exports;

    /**
     * @var Reports
     */
    public $reports;

    /**
     * @var ReportGroups
     */
    public $reportGroups;

    /**
     * @var Visualizations
     */
    public $visualizations;

    /**
     * @var Address
     */
    public $addressField;

    /**
     * @var AddressFormatter
     */
    public $addressFormatter;

    /**
     * @var Email
     */
    public $emailField;

    /**
     * @var EmailDropdown
     */
    public $emailDropdownField;

    /**
     * @var Name
     */
    public $nameField;

    /**
     * @var Phone
     */
    public $phoneField;

    /**
     * @var RegularExpression
     */
    public $regularExpressionField;

    /**
     * @var Url
     */
    public $urlField;

    /**
     * @var FieldUtilities
     */
    public $fieldUtilities;

    /**
     * @var NotificationEmails
     */
    public $notifications;

    /**
     * @var NotificationEmailEvents
     */
    public $notificationEvents;

    /**
     * @var Mailers
     */
    public $mailers;

    /**
     * @var EmailTemplates
     */
    public $emailTemplates;

    /**
     * @var SentEmails
     */
    public $sentEmails;

    /**
     * @var Redirects
     */
    public $redirects;

    /**
     * @var Sitemaps
     */
    public $sitemaps;

    /**
     * @var XmlSitemap
     */
    public $xmlSitemap;

    /**
     * @var UrlEnabledSections
     */
    public $urlEnabledSections;

    /**
     * @var Settings
     */
    public $settings;

    /**
     * @var Config
     */
    public $config;

    /**
     * @var Utilities
     */
    public $utilities;

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Forms
        $this->formGroups = new FormGroups();
        $this->forms = new Forms();
        $this->formFields = new FormFields();
        $this->formEntries = new FormEntries();
        $this->entryStatuses = new EntryStatuses();
        $this->frontEndFields = new FrontEndFields();
        $this->integrations = new Integrations();
        $this->rules = new Rules();

        // Metadata
        $this->optimize = new Optimize();
        $this->globalMetadata = new GlobalMetadata();
        $this->elementMetadata = new ElementMetadata();
        $this->schema = new Schema();

        // Campaigns
        $this->campaignTypes = new CampaignTypes();
        $this->campaignEmails = new CampaignEmails();

        // Fields
        $this->addressField = new Address();
        $this->addressFormatter = new AddressFormatter();
        $this->emailField = new Email();
        $this->emailDropdownField = new EmailDropdown();
        $this->nameField = new Name();
        $this->phoneField = new Phone();
        $this->regularExpressionField = new RegularExpression();
        $this->urlField = new Url();
        $this->fieldUtilities = new FieldUtilities();

        // Reports
        $this->dataSources = new DataSources();
        $this->twigDataSource = new TwigDataSource();
        $this->exports = new Exports();
        $this->reportGroups = new ReportGroups();
        $this->reports = new Reports();
        $this->visualizations = new Visualizations();

        // Email
        $this->emailTemplates = new EmailTemplates();
        $this->mailers = new Mailers();
        $this->notifications = new NotificationEmails();
        $this->notificationEvents = new NotificationEmailEvents();
        $this->sentEmails = new SentEmails();

        $this->redirects = new Redirects();
        $this->sitemaps = new Sitemaps();
        $this->xmlSitemap = new XmlSitemap();
        $this->urlEnabledSections = new UrlEnabledSections();

        // Sprout Base
        $this->config = new Config();
        $this->settings = new Settings();
        $this->utilities = new Utilities();
    }
}
