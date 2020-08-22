<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase;

use barrelstrength\sproutbase\app\campaigns\mailers\CopyPasteMailer;
use barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates;
use barrelstrength\sproutbase\app\email\events\NotificationEmailEvent;
use barrelstrength\sproutbase\app\email\events\notificationevents\EntriesSave;
use barrelstrength\sproutbase\app\email\events\RegisterMailersEvent;
use barrelstrength\sproutbase\app\email\mailers\DefaultMailer;
use barrelstrength\sproutbase\app\email\services\EmailTemplates;
use barrelstrength\sproutbase\app\email\services\Mailers;
use barrelstrength\sproutbase\app\email\services\NotificationEmailEvents;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\configs\CampaignsConfig;
use barrelstrength\sproutbase\config\configs\ControlPanelConfig;
use barrelstrength\sproutbase\config\configs\EmailPreviewConfig;
use barrelstrength\sproutbase\config\configs\FieldsConfig;
use barrelstrength\sproutbase\config\configs\FormsConfig;
use barrelstrength\sproutbase\config\configs\ListsConfig;
use barrelstrength\sproutbase\config\configs\NotificationsConfig;
use barrelstrength\sproutbase\config\configs\RedirectsConfig;
use barrelstrength\sproutbase\config\configs\ReportsConfig;
use barrelstrength\sproutbase\config\configs\SentEmailConfig;
use barrelstrength\sproutbase\config\configs\SeoConfig;
use barrelstrength\sproutbase\config\configs\SitemapsConfig;
use barrelstrength\sproutbase\config\services\App;
use barrelstrength\sproutbase\web\twig\Extension;
use Craft;
use craft\console\controllers\MigrateController;
use craft\db\MigrationManager;
use craft\events\ExceptionEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterCpSettingsEvent;
use craft\events\RegisterMigratorEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\ArrayHelper;
use craft\i18n\PhpMessageSource;
use craft\services\UserPermissions;
use craft\web\Application;
use craft\web\ErrorHandler;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use craft\web\View;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\mail\BaseMailer;
use yii\mail\MailEvent;

class SproutBase extends Module
{
    /**
     * $var array SPROUT_PLUGIN_IDS
     */
    const SPROUT_PLUGIN_IDS = [
        'sprout-campaigns',
        'sprout-email',
        'sprout-fields',
        'sprout-forms',
        'sprout-lists',
        'sprout-sent-email',
        'sprout-redirects',
        'sprout-reports',
        'sprout-seo',
        'sprout-sitemaps',
    ];

    /**
     * @var Config[] SPROUT_MODULES
     */
    const SPROUT_MODULES = [
        CampaignsConfig::class,
        ControlPanelConfig::class,
        EmailPreviewConfig::class,
        FieldsConfig::class,
        FormsConfig::class,
        ListsConfig::class,
        NotificationsConfig::class,
        RedirectsConfig::class,
        ReportsConfig::class,
        SentEmailConfig::class,
        SeoConfig::class,
        SitemapsConfig::class,
    ];

    const MODULE_ID = 'sprout';
    const MIGRATION_NAMESPACE = 'barrelstrength\\sproutbase\\migrations';
    const MIGRATION_PATH = '@vendor/barrelstrength/sprout-base/src/migrations';

    /**
     * @var App
     */
    public static $app;

    /**
     * @var string|null The translation category that this module translation messages should use. Defaults to the lowercase plugin handle.
     */
    public $t9nCategory;

    /**
     * @var string The language that the module messages were written in
     */
    public $sourceLanguage = 'en-US';

    /**
     * This code was largely copied from craft/base/Plugin
     *
     * @inheritDoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        // Set some things early in case there are any settings, and the settings model's
        // init() method needs to call Craft::t() or Plugin::getInstance().

        $this->t9nCategory = ArrayHelper::remove($config, 't9nCategory', $this->t9nCategory ?? strtolower(self::MODULE_ID));
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
            'app' => App::class,
        ]);

        self::$app = $this->get('app');

        $this->initMappings();
        $this->initPermissions();
        $this->initTemplateEvents();
        $this->initEmailEvents();
        $this->initConfigEvents();
    }

    public function initMappings()
    {
        Craft::setAlias('@sproutbase', $this->getBasePath());
        Craft::setAlias('@sproutbaseassets', $this->getBasePath().'/web/assets');
        Craft::setAlias('@sproutbaseassetbundles', $this->getBasePath().'/web/assetbundles');
        Craft::setAlias('@sproutbaselib', dirname(__DIR__).'/lib');

        $controllerMap = self::$app->config->getComponentMap('getControllerMap');

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'sproutbase\\config\\console\\controllers';
        } else {
            $this->controllerNamespace = 'sproutbase\\config\\controllers';
            $this->controllerMap = $controllerMap;
        }

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, static function(RegisterUrlRulesEvent $event) {
            $cpConfig = SproutBase::$app->config->getConfigByKey('control-panel');
            $event->rules = array_merge($event->rules, $cpConfig->getCpUrlRules());
        });
    }

    public function initPermissions()
    {
        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, static function(RegisterUserPermissionsEvent $event) {
            $event->permissions['Sprout Plugins'] = self::$app->config->getUserPermissions();
        });
    }

    public function initTemplateEvents()
    {
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['sprout'] = $this->getBasePath().DIRECTORY_SEPARATOR.'templates';
        });

        Craft::$app->view->registerTwigExtension(new Extension());
    }

    public function initEmailEvents()
    {
        Event::on(Application::class, Application::EVENT_INIT, static function() {
            SproutBase::$app->notificationEvents->registerNotificationEmailEventHandlers();
        });

        Event::on(BaseMailer::class, BaseMailer::EVENT_AFTER_SEND, static function(MailEvent $event) {
            SproutBase::$app->sentEmails->handleLogSentEmail($event);
        });

        Event::on(Mailers::class, Mailers::EVENT_REGISTER_MAILER_TYPES, static function(RegisterMailersEvent $event) {
            $event->mailers[] = new DefaultMailer();
            $event->mailers[] = new CopyPasteMailer();
        });

        Event::on(EmailTemplates::class, EmailTemplates::EVENT_REGISTER_EMAIL_TEMPLATES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = BasicTemplates::class;
        });

        Event::on(NotificationEmailEvents::class, NotificationEmailEvents::EVENT_REGISTER_EMAIL_EVENT_TYPES, static function(NotificationEmailEvent $event) {
            $event->events[] = EntriesSave::class;
        });
    }

    public function initConfigEvents()
    {
        Event::on(
            MigrateController::class,
            MigrateController::EVENT_REGISTER_MIGRATOR,
            static function(RegisterMigratorEvent $event) {

                if ($event->track === self::MODULE_ID) {
                    $event->migrator = Craft::createObject([
                        'class' => MigrationManager::class,
                        'track' => self::MODULE_ID,
                        'migrationNamespace' => self::MIGRATION_NAMESPACE,
                        'migrationPath' => self::MIGRATION_PATH,
                    ]);
                    $event->handled = true;
                }
            }
        );

        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS, static function(RegisterCpNavItemsEvent $event) {
            $event->navItems = SproutBase::$app->config->updateCpNavItems($event->navItems);
        });

        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_SETTINGS, static function(RegisterCpSettingsEvent $event) {
            if ($settingsPages = SproutBase::$app->config->getSproutCpSettings()) {
                $event->settings['Sprout Plugins'] = $settingsPages;
            }
        });

        Event::on(ErrorHandler::class, ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION, static function(ExceptionEvent $event) {
            SproutBase::$app->redirects->handleRedirectsOnException($event);
        });
    }
}
