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
     *
     * @return array
     */
    public function getDefaultLabels(Report $report): array;

    /**
     * Should return an array of records to use in the report
     *
     * @param Report $report
     *
     * @return array
     */
    public function getResults(Report $report): array;
}
