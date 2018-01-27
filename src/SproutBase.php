<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbase\events\RegisterMailersEvent;
use barrelstrength\sproutbase\mailers\DefaultMailer;
use barrelstrength\sproutbase\services\sproutemail\Mailers;
use barrelstrength\sproutbase\web\twig\variables\SproutBaseVariable;
use craft\events\DefineComponentsEvent;
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
     * @var string|null The translation category that this module translation messages should use. Defaults to the lowercased plugin handle.
     */
    public $t9nCategory;

    /**
     * @var string The language that the module messages were written in
     */
    public $sourceLanguage = 'en-US';

    /**
     * @todo - We copied from craft/base/plugin, ask P&T if this is the best way to do it
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

        // Register our base template path
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['sprout-base'] = $this->getBasePath().DIRECTORY_SEPARATOR.'templates';
        });

        // Register our Variables
        Event::on(CraftVariable::class, CraftVariable::EVENT_DEFINE_COMPONENTS, function(DefineComponentsEvent $event) {
            $event->components['sproutbase'] = SproutBaseVariable::class;
        });

        // Register Sprout Email Events
        Event::on(Application::class, Application::EVENT_INIT, function() {
            SproutBase::$app->notifications->registerDynamicEventHandler();
        });

        // Register Sprout Email Mailers
        Event::on(Mailers::class, Mailers::EVENT_REGISTER_MAILERS, function(RegisterMailersEvent $event) {
            $event->mailers[] = new DefaultMailer();
        });
    }
}
