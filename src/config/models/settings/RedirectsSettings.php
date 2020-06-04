<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\base\Settings;
use barrelstrength\sproutbase\SproutBase;
use Craft;

class RedirectsSettings extends Settings
{
    /**
     * @var string
     */
    public $pluginNameOverride = '';

    /**
     * @var string
     */
    public $structureId = '';

    /**
     * @var bool
     */
    public $enableRedirects = true;

    /**
     * @var bool
     */
    public $enable404RedirectLog = false;

    /**
     * @var int
     */
    public $total404Redirects = 250;

    /**
     * @var bool
     */
    public $trackRemoteIp = false;

    /**
     * @var string
     */
    public $redirectMatchStrategy = 'urlWithoutQueryStrings';

    /**
     * @var string
     */
    public $queryStringStrategy = 'removeQueryStrings';

    /**
     * @var string
     */
    public $excludedUrlPatterns;

    /**
     * @var int
     */
    public $cleanupProbability = 1000;

    public function getSettingsNavItem(): array
    {
        return [
            'label' => Craft::t('sprout','Redirects'),
            'url' => 'sprout/settings/redirects',
            'icon' => '@sproutbaseicons/plugins/redirects/icon.svg',
            'subnav' => [
                'redirects' => [
                    'label' => Craft::t('sprout', 'Redirects'),
                    'url' => 'sprout/settings/redirects',
                    'template' => 'sprout-base-redirects/settings/redirects'
                ]
            ]
        ];
    }
}

