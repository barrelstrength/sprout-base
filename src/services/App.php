<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services;

use barrelstrength\sproutbaseemail\services\Mailers;
use barrelstrength\sproutbaseemail\services\NotificationEmailEvents;
use barrelstrength\sproutbaseemail\services\NotificationEmails;
use barrelstrength\sproutbase\app\fields\services\EmailDropdown;
use barrelstrength\sproutbase\app\import\services\FieldImporter;
use barrelstrength\sproutbase\app\import\services\Seed;
use barrelstrength\sproutbase\app\import\services\SettingsImporter;
use barrelstrength\sproutbase\app\import\services\Bundles;
use barrelstrength\sproutbase\app\import\services\ElementImporter;
use barrelstrength\sproutbase\app\import\services\ImportUtilities;
use barrelstrength\sproutbase\app\reports\services\DataSources;
use barrelstrength\sproutbase\app\reports\services\Exports;
use barrelstrength\sproutbase\app\reports\services\ReportGroups;
use barrelstrength\sproutbase\app\reports\services\Reports;
use barrelstrength\sproutbase\app\fields\services\Utilities;
use barrelstrength\sproutbase\app\fields\services\Url;
use barrelstrength\sproutbase\app\fields\services\Phone;
use barrelstrength\sproutbase\app\fields\services\RegularExpression;
use barrelstrength\sproutbase\app\fields\services\Email;
use barrelstrength\sproutbase\app\fields\services\Address;
use barrelstrength\sproutbase\app\import\services\Importers;
use craft\base\Component;
use barrelstrength\sproutbaseemail\services\EmailTemplates as SproutEmail;

class App extends Component
{
    /**
     * @var Settings
     */
    public $settings;

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Sprout Base
        $this->settings = new Settings();
    }
}
