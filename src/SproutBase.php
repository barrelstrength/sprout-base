<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase;

use barrelstrength\sproutbase\controllers\SettingsController;
use barrelstrength\sproutbase\services\App;
use Craft;
use craft\events\RegisterCpSettingsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\ArrayHelper;
use craft\i18n\PhpMessageSource;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use craft\web\View;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\Module;

/**
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
    public $translationCategory = 'sprout-base-settings';

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

        Craft::setAlias('@sproutbase', $this->getBasePath());
        Craft::setAlias('@sprouticons', $this->getBasePath().'/web/assets/sprout');
        Craft::setAlias('@sproutbaseicons', $this->getBasePath().'/web/assets/icons');

        // Setup Controllers
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'sproutbase\\console\\controllers';
        } else {
            $this->controllerNamespace = 'sproutbase\\controllers';

            $this->controllerMap = [
                'settings' => SettingsController::class
            ];
        }

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getCpUrlRules());
        });

        // Setup Template Roots
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['sprout-base'] = $this->getBasePath().DIRECTORY_SEPARATOR.'templates';
        });

        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_SETTINGS, static function(RegisterCpSettingsEvent $event) {
            $event->settings['Sprout Settings'] = [
                'campaigns' => [
                    'url' => 'sprout/settings/campaigns',
                    'icon' => '@sprouticons/campaigns/icon.svg',
                    'label' => 'Campaigns'
                ],
                'email' => [
                    'url' => 'sprout/settings/email',
                    'icon' => '@sprouticons/email/icon.svg',
                    'label' => 'Notifications'
                ],
                'fields' => [
                    'url' => 'sprout/settings/fields',
                    'icon' => '@sprouticons/fields/icon.svg',
                    'label' => 'Fields'
                ],
                'forms' => [
                    'url' => 'sprout/settings/forms',
                    'icon' => '@sprouticons/forms/icon.svg',
                    'label' => 'Forms'
                ],
                'lists' => [
                    'url' => 'sprout/settings/lists',
                    'icon' => '@sprouticons/lists/icon.svg',
                    'label' => 'Lists'
                ],
                'redirects' => [
                    'url' => 'sprout/settings/redirects',
                    'icon' => '@sprouticons/redirects/icon.svg',
                    'label' => 'Redirects'
                ],
                'reports' => [
                    'url' => 'sprout/settings/reports',
                    'icon' => '@sprouticons/reports/icon.svg',
                    'label' => 'Reports'
                ],
                'sent-email' => [
                    'url' => 'sprout/settings/sent-email',
                    'icon' => '@sprouticons/sent-email/icon.svg',
                    'label' => 'Sent Email'
                ],
                'seo' => [
                    'url' => 'sprout/settings/seo',
                    'icon' => '@sprouticons/seo/icon.svg',
                    'label' => 'SEO'
                ],
                'sitemaps' => [
                    'url' => 'sprout/settings/sitemaps',
                    'icon' => '@sprouticons/sitemaps/icon.svg',
                    'label' => 'Sitemaps'
                ]
            ];
        });
    }

    public function getCpUrlRules()
    {
        return [
            // Settings
            'sprout/settings/<settingsSectionHandle:.*>' =>
                'sprout/settings/edit-settings-new',
        ];
    }
}
