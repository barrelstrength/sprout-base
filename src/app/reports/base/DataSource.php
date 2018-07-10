<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\base;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\app\reports\elements\Report;
use Craft;
use barrelstrength\sproutbase\app\reports\records\DataSource as DataSourceRecord;
use craft\base\Plugin;
use craft\helpers\UrlHelper;

/**
 * Class DataSource
 *
 * @package Craft
 */
abstract class DataSource
{
    use BaseSproutTrait;
    /**
     * @var int
     */
    public $dataSourceId;

    /**
     * @var string
     */
    protected $dataSourceSlug;

    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var Report()
     */
    protected $report;

    /**
     * DataSource constructor.
     *
     * @throws \ReflectionException
     */
    public function __construct()
    {
        // Get plugin class
        $pluginHandle = Craft::$app->getPlugins()->getPluginHandleByClass(get_class($this));

        $this->plugin = Craft::$app->getPlugins()->getPlugin($pluginHandle);

        // Build $dataSourceSlug: pluginname-datasourceclassname
        $pluginHandleWithoutSpaces = str_replace('-', '', $pluginHandle);

        $dataSourceClass = (new \ReflectionClass($this))->getShortName();

        $dataSourceSlug = $pluginHandleWithoutSpaces.'-'.$dataSourceClass;

        $this->dataSourceSlug = strtolower($dataSourceSlug);
    }

    /**
     * Returns an instance of the plugin that created this Data Source
     *
     * @return Plugin|null
     */
    final public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * Set a Report on our data source.
     *
     * @param Report|null $report
     */
    public function setReport(Report $report = null)
    {
        if (null === $report) {
            $report = new Report();
        }

        $this->report = $report;
    }

    /**
     * Should return a human readable name for your data source
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Should return an string containing the necessary HTML to capture user input
     *
     * @return null|string
     */
    public function getSettingsHtml()
    {
        return null;
    }

    /**
     * Should return an array of strings to be used as column headings in display/output
     *
     * @param Report $report
     * @param array  $settings
     *
     * @return array
     */
    public function getDefaultLabels(Report $report, array $settings = [])
    {
        return [];
    }

    /**
     * Should return an array of records to use in the report
     *
     * @param Report $report
     * @param array  $settings
     *
     * @return array
     */
    public function getResults(Report $report, array $settings = [])
    {
        return [];
    }

    /**
     * Give a Data Source a chance to prepare settings before they are processed by the Dynamic Name field
     *
     * @param array $settings
     *
     * @return null
     */
    public function prepSettings(array $settings)
    {
        return $settings;
    }

    /**
     * Validate the data sources settings
     *
     * @param array $settings
     * @param array $errors
     *
     * @return bool
     */
    public function validateSettings(array $settings = [], array &$errors)
    {
        return true;
    }

    /**
     * Returns the CP URL for the given data source
     *
     * @return string
     */
    public function getUrl($append = null)
    {
        $pluginHandle = Craft::$app->getRequest()->getSegment(1);

        $baseUrl = $pluginHandle.'/reports/';

        $appendedUrl = ltrim($append, '/');

        return UrlHelper::cpUrl($baseUrl.$appendedUrl);
    }

    /**
     * Allow a user to toggle the Allow Html setting.
     *
     * @return bool
     */
    public function isAllowHtmlEditable()
    {
        return false;
    }

    /**
     * Define the default value for the Allow HTML setting. Setting Allow HTML
     * to true enables a report to output HTML on the Results page.
     *
     * @return bool
     */
    public function getDefaultAllowHtml()
    {
        return false;
    }

    /**
     * Allows a user to disable a Data Source from displaying in the New Report dropdown
     *
     * @return bool|mixed
     */
    public function allowNew()
    {
        $dataSourceRecord = DataSourceRecord::findOne(['id' => $this->dataSourceId]);

        if ($dataSourceRecord != null) {
            return $dataSourceRecord->allowNew;
        }

        return true;
    }

    /**
     * Returns the total count of reports created based on the given data source
     *
     * @return int
     */
    final public function getReportCount()
    {
        return SproutBase::$app->reports->getCountByDataSourceId($this->dataSourceId);
    }
}
