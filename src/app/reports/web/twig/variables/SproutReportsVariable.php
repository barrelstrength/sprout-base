<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\reports\web\twig\variables;

use barrelstrength\sproutbase\app\reports\elements\Report;
use barrelstrength\sproutbase\SproutBase;
use yii\base\Exception;

class SproutReportsVariable
{
    /**
     * @return Report[]
     */
    public function getReports(): array
    {
        return SproutBase::$app->reports->getAllReports();
    }

    /**
     * @return null|Report[]
     */
    public function getReportGroups()
    {
        return SproutBase::$app->reportGroups->getReportGroups();
    }

    /**
     * @param $groupId
     *
     * @return array
     * @throws Exception
     */
    public function getReportsByGroupId($groupId): array
    {
        return SproutBase::$app->reports->getReportsByGroupId($groupId);
    }

    /**
     * @param array $row
     */
    public function addHeaderRow(array $row)
    {
        SproutBase::$app->twigDataSource->addHeaderRow($row);
    }

    /**
     * Add a single row of data to your report
     *
     * @param array $row
     */
    public function addRow(array $row)
    {
        SproutBase::$app->twigDataSource->addRow($row);
    }

    /**
     * Add multiple rows of data to your report
     *
     * @param array $rows
     *
     * @example array(
     *          array( ... ),
     *          array( ... )
     *          )
     *
     */
    public function addRows(array $rows)
    {
        SproutBase::$app->twigDataSource->addRows($rows);
    }

    /**
     * @return Report[]
     */
    public function getVisualizationAggregates(): array
    {
        return SproutBase::$app->visualizations->getAggregates();
    }
}