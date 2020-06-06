<?php

namespace barrelstrength\sproutbase\config\web\twig\variables;

use barrelstrength\sproutbase\app\campaigns\web\twig\variables\SproutCampaignsVariable;
use barrelstrength\sproutbase\app\email\web\twig\variables\SproutEmailVariable;
use barrelstrength\sproutbase\app\fields\web\twig\variables\SproutFieldsVariable;
use barrelstrength\sproutbase\app\forms\web\twig\variables\SproutFormsVariable;
use barrelstrength\sproutbase\app\metadata\web\twig\variables\SproutSeoVariable;
use barrelstrength\sproutbase\app\reports\web\twig\variables\SproutReportsVariable;
use barrelstrength\sproutbase\app\sitemaps\web\twig\variables\SproutSitemapVariable;
use yii\di\ServiceLocator;

class SproutVariable extends ServiceLocator
{
    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        $components = [
            'campaigns' => SproutCampaignsVariable::class,
            'email' => SproutEmailVariable::class,
            'fields' => SproutFieldsVariable::class,
            'forms' => SproutFormsVariable::class,
            'reports' => SproutReportsVariable::class,
            'seo' => SproutSeoVariable::class,
            'sitemaps' => SproutSitemapVariable::class
        ];

        $config['components'] = $components;

        parent::__construct($config);
    }
}