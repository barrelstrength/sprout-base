<?php

namespace barrelstrength\sproutbase\app\reports\base;

use barrelstrength\sproutbase\app\reports\elements\Report;
use craft\base\SavableComponentInterface;

interface DataSourceInterface extends SavableComponentInterface
{
    /**
     * A description for the Data Source
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Should return an array of strings to be used as column headings in display/output
     *
     * @param Report $report
     * @param array  $settings
     *
     * @return array
     */
    public function getDefaultLabels(Report $report, array $settings = []): array;

    /**
     * Should return an array of records to use in the report
     *
     * @param Report $report
     * @param array  $settings Not in use. Use $report->getSettings() instead.
     *
     * @return array
     * @todo - Deprecated $settings param in 1.0. Will be removed in 2.0.
     *
     */
    public function getResults(Report $report, array $settings = []): array;
}
