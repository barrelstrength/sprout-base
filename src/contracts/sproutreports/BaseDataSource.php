<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\contracts\sproutreports;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutreports\elements\Report;
use Craft;
use barrelstrength\sproutbase\records\sproutreports\DataSource;
use craft\helpers\UrlHelper;

/**
 * Class BaseDataSource
 *
 * @package Craft
 */
abstract class BaseDataSource
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
     * @var string
     */
    protected $plugin;

    /**
     * @var Report()
     */
    protected $report;

    /**
     * BaseDataSource constructor.
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
     * @param array       $settings
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
     * @param array       $settings
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
     * Returns the CP URL for the given data source with the option to append to it once composed
     *
     * @legend
     * Breaks apart the data source id and transforms its components into a URL friendly string
     *
     * @example
     * sproutReports.customQuery > sproutreports/customquery
     * sproutreports.customquery > sproutreports/customquery
     *
     * @see getDataSourceSlug()
     *
     * @param string $append
     *
     * @return string
     */
    public function getUrl($append = null)
    {
        $pluginHandle = Craft::$app->getRequest()->getSegment(1);

        $baseUrl = $pluginHandle.'/reports/'.$this->dataSourceId.'-'.$this->getDataSourceSlug().'/';

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
        $dataSourceRecord = DataSource::findOne(['id' => $this->dataSourceId]);

        if ($dataSourceRecord != null) {
            return $dataSourceRecord->allowNew;
        }

        return true;
    }

    /**
     * Returns a fully qualified string that uniquely identifies the given data source
     *
     * @format {plugin}-{source}
     * 1. {plugin} should be the lower case version of the plugin handle
     * 3. {source} should be the lower case version of your data source without prefixes or suffixes
     *
     * @example
     * - SproutFormsSubmissionsDataSource   > sproutforms-submissions
     * - CustomQuery > sproutreports-customquery
     *
     * @return string
     */
    final public function getDataSourceSlug()
    {
        return $this->dataSourceSlug;
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
