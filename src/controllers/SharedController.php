<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\controllers;

use barrelstrength\sproutbase\base\SharedPermissionsInterface;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;

/**
 * @property string                          $defaultPluginHandle
 * @property string                          $defaultViewContext
 * @property SharedPermissionsInterface|null $sharedSettingsModel
 */
abstract class SharedController extends Controller
{
    /**
     * An array of mapped permissions so we can always use the base
     * plugins permission as the target
     *
     * Via Sprout Forms
     *   [
     *     'sproutForms-viewReports' => 'sproutReports-viewReports',
     *     'sproutForms-editReports' => 'sproutReports-editReports',
     *   ]
     *
     * Via Sprout Reports
     *   [
     *     'sproutReports-viewReports' => 'sproutReports-viewReports',
     *     'sproutReports-editReports' => 'sproutReports-editReports',
     *   ]
     *
     * @var array
     */
    protected $permissions = [];

    /**
     * The plugin handle of the PRIMARY plugin that implements these features
     *
     * Example:
     * sprout-base-reports would use 'sprout-reports' as the default
     * sprout-base-sitemaps would use 'sprout-sitemaps' as the default
     *
     * @var string
     */
    protected $pluginHandle;

    /**
     * Most likely the second segment in the URL route.
     *
     * This is used when building the currentBaseUrl so that certain URLs
     * can adapt to the plugin they are being used in.
     *
     * @see $this->currentBaseUrl for examples
     *
     * @var string|null
     */
    protected $pluginSection;

    /**
     * An alternative variable that can be used if plugin-handle doesn't meet
     * the requirements of how we need to differentiate between behaviors.
     *
     * Often, this will be the same as the plugin handle.
     *
     * @var string
     */
    protected $viewContext;

    /**
     * The base URL to be used when a module is being shared between plugins
     *
     * Example:
     * sprout-base-email links to different edit pages in Sprout Email and
     * Sprout Forms, depending on which is installed:
     * - sprout-email/<pluginSection:notifications>/edit/123
     * - sprout-forms/<pluginSection:notifications>/edit/123
     *
     * @var string
     */
    protected $currentBaseUrl;

    public function init()
    {
        parent::init();

        $routeParams = Craft::$app->getUrlManager()->getRouteParams();

        $this->pluginHandle = $routeParams['pluginHandle']
            ?? Craft::$app->getRequest()->getBodyParam('pluginHandle')
            ?? $this->getDefaultPluginHandle();
        $this->viewContext = $routeParams['viewContext'] ?? $this->getDefaultViewContext();
        $this->pluginSection = $routeParams['pluginSection'] ?? null;

        $this->currentBaseUrl = $this->getCurrentBaseUrl();

        if ($this->getSharedSettingsModel()) {
            $this->permissions = SproutBase::$app->settings->getPluginPermissions(
                $this->getSharedSettingsModel(),
                $this->getDefaultPluginHandle(),
                $this->pluginHandle
            );
        }
    }

    /**
     * The plugin handle of the primary plugin this module represents
     *
     * @return string
     */
    abstract public function getDefaultPluginHandle(): string;

    /**
     * Give controller a chance to override viewContext
     *
     * @return string
     */
    protected function getDefaultViewContext(): string
    {
        return $this->getDefaultPluginHandle();
    }
    
    /**
     * The base URL to use when building URLs that may exist in multiple plugins
     *
     * @return string
     */
    protected function getCurrentBaseUrl(): string
    {
        $baseUriSegments = array_filter([$this->pluginHandle, $this->pluginSection]);
        $baseUri = implode('/', $baseUriSegments);

        return UrlHelper::cpUrl($baseUri).'/';
    }

    /**
     * The Settings module for the settings this module uses
     *
     * Example:
     * return new Settings();
     *
     * @return SharedPermissionsInterface|null
     */
    protected function getSharedSettingsModel()
    {
        return null;
    }
}
