<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase;

use barrelstrength\sproutbase\app\email\services\Email;
use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbase\controllers\SettingsController;
use barrelstrength\sproutbase\app\email\controllers\NotificationsController;
use barrelstrength\sproutbase\app\email\events\RegisterMailersEvent;
use barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates;
use barrelstrength\sproutbase\app\email\mailers\DefaultMailer;

use barrelstrength\sproutbase\app\email\services\Mailers;
use barrelstrength\sproutbase\app\fields\controllers\AddressController;
use barrelstrength\sproutbase\app\fields\controllers\FieldsController;
use barrelstrength\sproutbase\app\fields\web\twig\variables\SproutFieldsVariable;
use barrelstrength\sproutbase\app\email\web\twig\variables\SproutEmailVariable;
use barrelstrength\sproutbase\app\reports\controllers\ReportsController;
use barrelstrength\sproutbase\app\import\web\twig\variables\SproutImportVariable;
use craft\events\RegisterComponentTypesEvent;
use craft\web\Application;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;
use \yii\base\Module;
use craft\web\View;
use craft\events\RegisterTemplateRootsEvent;
use craft\helpers\ArrayHelper;
use craft\i18n\PhpMessageSource;
use Craft;

use barrelstrength\sproutbase\services\App;

class SproutBase extends Module
{
    use BaseSproutTrait;

    /**
     * @var string
     */
    public $handle;

    /**
     * @var App
     */
    public static $app;

    /**
     * Identify our plugin for BaseSproutTrait
     *
     * @var string
     */
    public static $pluginId = 'sprout-base';

    /**
     * @var string|null The translation category that this module translation messages should use. Defaults to the lowercase plugin handle.
     */
    public $t9nCategory;

    /**
     * @var string The language that the module messages were written in
     */
    public $sourceLanguage = 'en-US';

    /**
     * @var array
     */
    public $controllerMap = [
        'settings'=> SettingsController::class,
        'notifications' => NotificationsController::class,
        'fields' => FieldsController::class,
        'fields-address' => AddressController::class,
        'reports' => ReportsController::class
    ];

    /**
     * @todo - Copied from craft/base/plugin. Ask P&T if this is the best approach
     *
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        // Set some things early in case there are any settings, and the settings model's
        // init() method needs to call Craft::t() or Plugin::getInstance().

        $this->handle = 'sprout-base';
        $this->t9nCategory = ArrayHelper::remove($config, 't9nCategory', $this->t9nCategory ?? strtolower($this->handle));
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

    public function init()
    {
        parent::init();

        self::$app = new App();

        Craft::setAlias('@sproutbase', $this->getBasePath());
        Craft::setAlias('@sproutbaselib', dirname(__DIR__, 2).'/sprout-base/lib');
        Craft::setAlias('@sproutbaseicons', $this->getBasePath().'/sproutbase/web/assets/icons');

        // Register our base template path
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['sprout-base'] = $this->getBasePath().DIRECTORY_SEPARATOR.'templates';
            $e->roots['sprout-base-email'] = $this->getBasePath().DIRECTORY_SEPARATOR.'app/email/templates';
            $e->roots['sprout-base-fields'] = $this->getBasePath().DIRECTORY_SEPARATOR.'app/fields/templates';
            $e->roots['sprout-base-forms'] = $this->getBasePath().DIRECTORY_SEPARATOR.'app/forms/templates';
            $e->roots['sprout-base-import'] = $this->getBasePath().DIRECTORY_SEPARATOR.'app/import/templates';
            $e->roots['sprout-base-lists'] = $this->getBasePath().DIRECTORY_SEPARATOR.'app/lists/templates';
            $e->roots['sprout-base-notes'] = $this->getBasePath().DIRECTORY_SEPARATOR.'app/notes/templates';
            $e->roots['sprout-base-reports'] = $this->getBasePath().DIRECTORY_SEPARATOR.'app/reports/templates';
            $e->roots['sprout-base-seo'] = $this->getBasePath().DIRECTORY_SEPARATOR.'app/seo/templates';
        });

        // Register our Variables
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $variable = $event->sender;
            $variable->set('sproutEmail', SproutEmailVariable::class);
            $variable->set('sproutFields', SproutFieldsVariable::class);
            $variable->set('sproutImport', SproutImportVariable::class);
        });

        // Register Sprout Email Events
        Event::on(Application::class, Application::EVENT_INIT, function() {
            SproutBase::$app->notificationEvents->registerNotificationEmailEventHandlers();
        });

        // Register Sprout Email Mailers
        Event::on(Mailers::class, Mailers::EVENT_REGISTER_MAILER_TYPES, function(RegisterMailersEvent $event) {
            $event->mailers[] = new DefaultMailer();
        });

        // Register Sprout Email Templates
        Event::on(Email::class, Email::EVENT_REGISTER_EMAIL_TEMPLATES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = BasicTemplates::class;
        });
    }
}
