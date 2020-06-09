<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\twig\variables;

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