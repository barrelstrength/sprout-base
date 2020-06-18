<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\twig\variables;

use barrelstrength\sproutbase\SproutBase;

class ReportsVariable
{
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
     * @example [
     *   [ ... ],
     *   [ ... ],
     * ]
     */
    public function addRows(array $rows)
    {
        SproutBase::$app->twigDataSource->addRows($rows);
    }
}