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
use barrelstrength\sproutbase\app\campaigns\web\twig\variables\SproutCampaignsVariable;
use barrelstrength\sproutbase\app\email\controllers\MailersController;
use barrelstrength\sproutbase\app\email\controllers\NotificationsController;
use barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates;
use barrelstrength\sproutbase\app\email\events\RegisterMailersEvent;
use barrelstrength\sproutbase\app\email\mailers\DefaultMailer;
use barrelstrength\sproutbase\app\email\services\EmailTemplates;
use barrelstrength\sproutbase\app\email\services\Mailers;
use barrelstrength\sproutbase\app\email\web\twig\variables\SproutEmailVariable;
use barrelstrength\sproutbase\app\fields\controllers\AddressController;
use barrelstrength\sproutbase\app\fields\controllers\FieldsController;
use barrelstrength\sproutbase\app\fields\web\twig\variables\SproutFieldsVariable;
use barrelstrength\sproutbase\app\redirects\controllers\RedirectsController;
use barrelstrength\sproutbase\app\reports\controllers\DataSourcesController;
use barrelstrength\sproutbase\app\reports\controllers\ReportsController;
use barrelstrength\sproutbase\app\reports\datasources\CustomQuery;
use barrelstrength\sproutbase\app\reports\datasources\CustomTwigTemplate;
use barrelstrength\sproutbase\app\reports\datasources\Users;
use barrelstrength\sproutbase\app\reports\services\DataSources;
use barrelstrength\sproutbase\app\reports\web\twig\variables\SproutReportsVariable;
use barrelstrength\sproutbase\app\reports\widgets\Number;
use barrelstrength\sproutbase\app\reports\widgets\Visualization;
use barrelstrength\sproutbase\app\sentemail\controllers\SentEmailController;
use barrelstrength\sproutbase\app\sitemaps\controllers\SitemapsController;
use barrelstrength\sproutbase\app\sitemaps\controllers\XmlSitemapController;
use barrelstrength\sproutbase\app\sitemaps\web\twig\variables\SproutSitemapVariable;
use barrelstrength\sproutbase\config\configs\CampaignsConfig;
use barrelstrength\sproutbase\config\configs\EmailConfig;
use barrelstrength\sproutbase\config\configs\FieldsConfig;
use barrelstrength\sproutbase\config\configs\FormsConfig;
use barrelstrength\sproutbase\config\configs\GeneralConfig;
use barrelstrength\sproutbase\config\configs\RedirectsConfig;
use barrelstrength\sproutbase\config\configs\ReportsConfig;
use barrelstrength\sproutbase\config\configs\SentEmailConfig;
use barrelstrength\sproutbase\config\configs\SeoConfig;
use barrelstrength\sproutbase\config\configs\SitemapsConfig;
use barrelstrength\sproutbase\config\controllers\SettingsController;
use barrelstrength\sproutbase\config\services\App;
use barrelstrength\sproutbase\config\services\Config;
use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterCpSettingsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\ArrayHelper;
use craft\i18n\PhpMessageSource;
use craft\services\Dashboard;
use craft\services\UserPermissions;
use craft\web\Application;
use craft\web\twig\variables\Cp;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
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
        $this->initConfigEvents();
        $this->initTemplateEvents();
        $this->initEmailEvents();
        $this->initReportEvents();
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

    public function initConfigEvents()
    {
        Event::on(Config::class, Config::EVENT_REGISTER_SPROUT_CONFIG, static function(RegisterComponentTypesEvent $event) {

//            $enabledSproutConfigClasses = self::$app->config->getSproutConfigs();
//
//            foreach ($enabledSproutConfigClasses as $sproutConfigClass) {
//                $event->types[] = $sproutConfigClass;
//            }

            $event->types[] = CampaignsConfig::class;
            $event->types[] = FieldsConfig::class;
            $event->types[] = FormsConfig::class;
//            $event->types[] = ListsSettings::class;
            $event->types[] = EmailConfig::class;
            $event->types[] = RedirectsConfig::class;
            $event->types[] = ReportsConfig::class;
            $event->types[] = SentEmailConfig::class;
            $event->types[] = SeoConfig::class;
            $event->types[] = SitemapsConfig::class;
            $event->types[] = GeneralConfig::class;
//
//            \Craft::dd($event);
        });

        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS, static function(RegisterCpNavItemsEvent $event) {
            $sproutNavItems = SproutBase::$app->config->buildSproutNavItems();
            $event->navItems = SproutBase::$app->config->updateCpNavItems($event->navItems, $sproutNavItems);
        });

        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_SETTINGS, static function(RegisterCpSettingsEvent $event) {
            if ($settingsPages = self::$app->config->getSproutCpSettings()) {
                $event->settings['Sprout Settings'] = $settingsPages;
            }
        });

        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions['Sprout Settings'] = $this->getUserPermissions();
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getCpUrlRules());
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getSiteUrlRules());
        });
    }

    public function initTemplateEvents()
    {
        // Setup Template Roots
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['sprout-base'] = $this->getBasePath().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'templates';
            $e->roots['sprout-base-sitemaps'] = $this->getBasePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'sitemaps'.DIRECTORY_SEPARATOR.'templates';
            $e->roots['sprout-base-redirects'] = $this->getBasePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'redirects'.DIRECTORY_SEPARATOR.'templates';
            $e->roots['sprout-base-sent-email'] = $this->getBasePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'sentemail'.DIRECTORY_SEPARATOR.'templates';
            $e->roots['sprout-base-email'] = $this->getBasePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'email'.DIRECTORY_SEPARATOR.'templates';
            $e->roots['sprout-base-fields'] = $this->getBasePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'fields'.DIRECTORY_SEPARATOR.'templates';
            $e->roots['sprout-base-reports'] = $this->getBasePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'reports'.DIRECTORY_SEPARATOR.'templates';
            $e->roots['sprout-base-campaigns'] = $this->getBasePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'campaigns'.DIRECTORY_SEPARATOR.'templates';
        });

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, static function(Event $event) {
            $event->sender->set('sproutCampaigns', SproutCampaignsVariable::class);
            $event->sender->set('sproutEmail', SproutEmailVariable::class);
            $event->sender->set('sproutFields', SproutFieldsVariable::class);
            $event->sender->set('sproutReports', SproutReportsVariable::class);
            $event->sender->set('sproutSitemap', SproutSitemapVariable::class);
        });
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

    /**
     * @return array
     */
    public function getUserPermissions(): array
    {
        $settings = self::$app->settings->getSettings();

        $permissions = [];
        foreach ($settings as $setting) {
            // Don't worry about it if no permissions exist
            if (!method_exists($setting, 'getUserPermissions')) {
                continue;
            }

            foreach ($setting->getUserPermissions() as $permissionName => $permissionArray) {
                $permissions[$permissionName] = $permissionArray;
            }
        }

        ksort($permissions, SORT_NATURAL);

        return $permissions;
    }

    private function getCpUrlRules(): array
    {
        $configTypes = self::$app->config->getConfigs();

        $urlRules = [];
        foreach ($configTypes as $configType) {
            $rules = $configType->getCpUrlRules();
            foreach ($rules as $route => $details) {
                $urlRules[$route] = $details;
            }
        }

        return $urlRules;
    }

    private function getSiteUrlRules(): array
    {
        $configTypes = self::$app->config->getConfigs();

        $urlRules = [];
        foreach ($configTypes as $configType) {
            $rules = $configType->getSiteUrlRules();
            foreach ($rules as $route => $details) {
                $urlRules[$route] = $details;
            }
        }

        return $urlRules;
    }
}
