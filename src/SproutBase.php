<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase;

use barrelstrength\sproutbase\app\campaigns\controllers\CampaignEmailController;
use barrelstrength\sproutbase\app\campaigns\controllers\CampaignTypeController;
use barrelstrength\sproutbase\app\campaigns\mailers\CopyPasteMailer;
use barrelstrength\sproutbase\app\email\controllers\MailersController;
use barrelstrength\sproutbase\app\email\controllers\NotificationsController;
use barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates;
use barrelstrength\sproutbase\app\email\events\RegisterMailersEvent;
use barrelstrength\sproutbase\app\email\mailers\DefaultMailer;
use barrelstrength\sproutbase\app\email\services\EmailTemplates;
use barrelstrength\sproutbase\app\email\services\Mailers;
use barrelstrength\sproutbase\app\fields\controllers\AddressController;
use barrelstrength\sproutbase\app\fields\controllers\FieldsController;
use barrelstrength\sproutbase\app\forms\controllers\ChartsController;
use barrelstrength\sproutbase\app\forms\controllers\EntriesController;
use barrelstrength\sproutbase\app\forms\controllers\EntryStatusesController;
use barrelstrength\sproutbase\app\forms\controllers\FormsController;
use barrelstrength\sproutbase\app\forms\controllers\GroupsController;
use barrelstrength\sproutbase\app\forms\controllers\IntegrationsController;
use barrelstrength\sproutbase\app\forms\controllers\RulesController;
use barrelstrength\sproutbase\app\metadata\controllers\GlobalMetadataController;
use barrelstrength\sproutbase\app\redirects\controllers\RedirectsController;
use barrelstrength\sproutbase\app\reports\controllers\DataSourcesController;
use barrelstrength\sproutbase\app\reports\controllers\ReportsController;
use barrelstrength\sproutbase\app\reports\datasources\CustomQuery;
use barrelstrength\sproutbase\app\reports\datasources\CustomTwigTemplate;
use barrelstrength\sproutbase\app\reports\datasources\Users;
use barrelstrength\sproutbase\app\reports\services\DataSources;
use barrelstrength\sproutbase\app\reports\widgets\Number;
use barrelstrength\sproutbase\app\reports\widgets\Visualization;
use barrelstrength\sproutbase\app\sentemail\controllers\SentEmailController;
use barrelstrength\sproutbase\app\sitemaps\controllers\SitemapsController;
use barrelstrength\sproutbase\app\sitemaps\controllers\XmlSitemapController;
use barrelstrength\sproutbase\config\controllers\SettingsController;
use barrelstrength\sproutbase\config\services\App;
use barrelstrength\sproutbase\config\services\Config;
use barrelstrength\sproutbase\config\web\twig\Extension;
use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterCpSettingsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\ArrayHelper;
use craft\i18n\PhpMessageSource;
use craft\services\Dashboard;
use craft\services\UserPermissions;
use craft\web\Application;
use craft\web\twig\variables\Cp;
use craft\web\View;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\mail\BaseMailer;
use yii\mail\MailEvent;

/**
 * @property array    $userPermissions
 * @property string[] $cpUrlRules
 */
class SproutBase extends Module
{
    /**
     * @var App
     */
    public static $app;

    /**
     * @var string
     */
    public $translationCategory = 'sprout';

    /**
     * @var string|null The translation category that this module translation messages should use. Defaults to the lowercase plugin handle.
     */
    public $t9nCategory;

    /**
     * @var string The language that the module messages were written in
     */
    public $sourceLanguage = 'en-US';

    /**
     * This code was copied from craft/base/Plugin
     *
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        // Set some things early in case there are any settings, and the settings model's
        // init() method needs to call Craft::t() or Plugin::getInstance().

        $this->t9nCategory = ArrayHelper::remove($config, 't9nCategory', $this->t9nCategory ?? strtolower($this->translationCategory));
        $this->sourceLanguage = ArrayHelper::remove($config, 'sourceLanguage', $this->sourceLanguage);

        if (($basePath = ArrayHelper::remove($config, 'basePath')) !== null) {
            $this->setBasePath($basePath);
        }

        // Translation category
        $i18n = Craft::$app->getI18n();
        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (!isset($i18n->translations[$this->t9nCategory]) && !isset($i18n->translations[$this->t9nCategory.'*'])) {
            $i18n->translations[$this->t9nCategory] = [
                'class' => PhpMessageSource::class,
                'sourceLanguage' => $this->sourceLanguage,
                'basePath' => $this->getBasePath().DIRECTORY_SEPARATOR.'translations',
                'allowOverrides' => true,
            ];
        }

        // Set this as the global instance of this plugin class
        static::setInstance($this);

        parent::__construct($id, $parent, $config);
    }

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->setComponents([
            'app' => App::class
        ]);

        self::$app = $this->get('app');

        $this->initMappings();
        $this->initPermissions();
        $this->initTemplateEvents();
        $this->initEmailEvents();
        $this->initReportEvents();
        $this->initConfigEvents();

//        $dog = new Url
    }

    public function initMappings()
    {
        Craft::setAlias('@sproutbase', $this->getBasePath());
        Craft::setAlias('@sproutbaseicons', $this->getBasePath().'/config/web/assets/icons');
        Craft::setAlias('@sproutbaselib', dirname(__DIR__).'/lib');

        // Setup Controllers
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'sproutbase\\config\\console\\controllers';
        } else {
            $this->controllerNamespace = 'sproutbase\\config\\controllers';

            $this->controllerMap = [
                'charts' => ChartsController::class,
                'entries' => EntriesController::class,
                'entry-statuses' => EntryStatusesController::class,
                'form-fields' => FieldsController::class,
                'forms' => FormsController::class,
                'groups' => GroupsController::class,
                'integrations' => IntegrationsController::class,
                'rules' => RulesController::class,
                'global-metadata' => GlobalMetadataController::class,
                'campaign-email' => CampaignEmailController::class,
                'campaign-type' => CampaignTypeController::class,
                'copy-paste' => CopyPasteMailer::class,
                'reports' => ReportsController::class,
                'data-sources' => DataSourcesController::class,
                'fields' => FieldsController::class,
                'fields-address' => AddressController::class,
                'mailers' => MailersController::class,
                'notifications' => NotificationsController::class,
                'sent-email' => SentEmailController::class,
                'redirects' => RedirectsController::class,
                'sitemaps' => SitemapsController::class,
                'xml-sitemap' => XmlSitemapController::class,
                'settings' => SettingsController::class,
            ];
        }
    }

    public function initPermissions()
    {
        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions['Sprout Settings'] = $this->getUserPermissions();
        });
    }

    public function initTemplateEvents()
    {
        // Setup Template Roots
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['sprout'] = $this->getBasePath().DIRECTORY_SEPARATOR.'templates';
        });

        Craft::$app->view->registerTwigExtension(new Extension());
    }

    public function initEmailEvents()
    {
        // Email Tracking
        Event::on(BaseMailer::class, BaseMailer::EVENT_AFTER_SEND, static function(MailEvent $event) {
            $sentEmailSettings = SproutBase::$app->settings->getSettingsByKey('sent-email');
            if ($sentEmailSettings->enableSentEmails) {
                SproutBase::$app->sentEmails->logSentEmail($event);
            }
        });

        // Register Sprout Email Events
        Event::on(Application::class, Application::EVENT_INIT, static function() {
            SproutBase::$app->notificationEvents->registerNotificationEmailEventHandlers();
        });

        // Register Sprout Email Mailers
        Event::on(Mailers::class, Mailers::EVENT_REGISTER_MAILER_TYPES, static function(RegisterMailersEvent $event) {
            $event->mailers[] = new DefaultMailer();
            $event->mailers[] = new CopyPasteMailer();
        });

        // Register Sprout Email Templates
        Event::on(EmailTemplates::class, EmailTemplates::EVENT_REGISTER_EMAIL_TEMPLATES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = BasicTemplates::class;
        });
    }

    public function initReportEvents()
    {
        Event::on(DataSources::class, DataSources::EVENT_REGISTER_DATA_SOURCES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = CustomQuery::class;
            $event->types[] = CustomTwigTemplate::class;
            $event->types[] = Users::class;
        });

        Event::on(Dashboard::class, Dashboard::EVENT_REGISTER_WIDGET_TYPES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = Number::class;
            $event->types[] = Visualization::class;
        });
    }

    public function initConfigEvents()
    {
        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS, static function(RegisterCpNavItemsEvent $event) {
            $sproutNavItems = SproutBase::$app->config->buildSproutNavItems();
            $event->navItems = SproutBase::$app->config->updateCpNavItems($event->navItems, $sproutNavItems);
        });

        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_SETTINGS, static function(RegisterCpSettingsEvent $event) {
            if ($settingsPages = self::$app->config->getSproutCpSettings()) {
                $event->settings['Sprout Settings'] = $settingsPages;
            }
        });
    }

    /**
     * @return array
     */
    public function getUserPermissions(): array
    {
        $configTypes = self::$app->config->getConfigs();

        $permissions = [];
        foreach ($configTypes as $configType) {
            // Don't worry about it if no permissions exist
            if (!method_exists($configType, 'getUserPermissions')) {
                continue;
            }

            foreach ($configType->getUserPermissions() as $permissionName => $permissionArray) {
                $permissions[$permissionName] = $permissionArray;
            }
        }

        ksort($permissions, SORT_NATURAL);

        return $permissions;
    }
}
