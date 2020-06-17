<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\twig\variables;

use barrelstrength\sproutbase\config\services\App as SproutBaseApp;
use barrelstrength\sproutbase\SproutBase;
use yii\di\ServiceLocator;

class SproutVariable extends ServiceLocator
{
    /**
     * @var SproutBaseApp|null The Sprout application class
     */
    public $app;

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        $components = [
            'forms' => FormsVariable::class,
            'reports' => ReportsVariable::class,
            'seo' => SeoVariable::class,
        ];

        $config['components'] = $components;

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        $this->app = SproutBase::$app;
    }
}