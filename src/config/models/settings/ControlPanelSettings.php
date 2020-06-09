<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\config\base\Settings;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

/**
 *
 * @property array $settingsNavItem
 */
class ControlPanelSettings extends Settings
{
    public $cp;

//    public $campaignsPluginName = '';
//
//    public $emailPluginName = '';
//
//    public $formsPluginName = '';
//
//    public $listsPluginName = '';
//
//    public $reportsPluginName = '';
//
//    public $redirectsPluginName = '';
//
//    public $seoPluginName = '';
//
//    public $sentEmailPluginName = '';
//
//    public $sitemapsPluginName = '';

    /**
     * @return array|array[]
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsNavItem(): array
    {
        $configs = SproutBase::$app->config->getConfigs();

        $currentSite = $this->getCurrentSite();

        $cpSettingsRows = [];
        $i = 0;
        foreach ($configs as $config) {
            if (!$config->showCpDisplaySettings()) {
                continue;
            }

            $headingInputHtml = Craft::$app->getView()->renderTemplate('_includes/forms/text', [
                'name' => 'modules['.$i.'][moduleKey]',
                'value' => $config->getKey(),
                'type' => 'hidden'
            ]);

            $enabledInputHtml = Craft::$app->getView()->renderTemplate('_includes/forms/lightswitch', [
                'name' => 'modules['.$i.'][enabled]',
                'on' => true,
                'value' => $currentSite->id,
                'small' => true
            ]);

            $cpSettingsRows[] = [
                'heading' => $config::displayName().$headingInputHtml,
                'enabled' => $enabledInputHtml,
                'displayName' => '',
            ];

            $i++;
        }

        return [
            'control-panel' => [
                'label' => Craft::t('sprout', 'Control Panel'),
                'template' => 'sprout/config/_settings/control-panel',
                'variables' => [
                    'cpSettingsRows' => $cpSettingsRows
                ]
            ],
            'welcome' => [
                'label' => Craft::t('sprout', 'Welcome'),
                'template' => 'sprout/config/_settings/welcome'
            ]
        ];
    }
}

