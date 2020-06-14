<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\base;

use barrelstrength\sproutbase\app\reports\elements\Report;
use barrelstrength\sproutbase\SproutBase;
use craft\base\SavableComponent;
use craft\helpers\UrlHelper;

/**
 * @property string $description
 * @property int    $reportCount
 * @property string $defaultEmailColumn
 * @property bool   $defaultAllowHtml
 */
abstract class DataSource extends SavableComponent implements DataSourceInterface
{
    use DataSourceTrait;

    /**
     * This value indicates whether a Report is being generated for Export
     *
     * This is set to true when exporting data, so a report can do something
     * like show HTML in the CP report view and exclude that HTML when exporting.
     *
     * @var bool
     */
    public $isExport = false;

    /**
     * @return bool
     */
    public function isEmailColumnEditable(): bool
    {
        return true;
    }

    /**
     * getDefaultEmailColumn is only used when isEmailColumnEditable is set to false.
     *
     * @return string
     */
    public function getDefaultEmailColumn(): string
    {
        return '';
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
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getDefaultLabels(Report $report, array $settings = []): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getResults(Report $report, array $settings = []): array
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
    public function validateSettings(array $settings = [], array &$errors = []): bool
    {
        return true;
    }

    /**
     * Allow a user to toggle the Allow Html setting.
     *
     * @return bool
     */
    public function isAllowHtmlEditable(): bool
    {
        return false;
    }

    /**
     * Define the default value for the Allow HTML setting. Setting Allow HTML
     * to true enables a report to output HTML on the Results page.
     *
     * @return bool
     */
    public function getDefaultAllowHtml(): bool
    {
        return false;
    }

    /**
     * Returns the total count of reports created based on the given data source
     *
     * @return int
     */
    final public function getReportCount(): int
    {
        return SproutBase::$app->reports->getCountByDataSourceId($this->id);
    }
}
