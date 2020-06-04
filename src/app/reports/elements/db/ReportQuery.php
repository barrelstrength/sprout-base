<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\elements\db;

use barrelstrength\sproutbase\app\reports\base\DataSource;
use barrelstrength\sproutbase\app\reports\records\DataSource as DataSourceRecord;
use Craft;
use craft\elements\db\ElementQuery;
use craft\errors\MissingComponentException;
use craft\helpers\Db;

class ReportQuery extends ElementQuery
{
    public $id;

    public $name;

    public $hasNameFormat;

    public $nameFormat;

    public $handle;

    public $description;

    public $allowHtml;

    public $sortOrder;

    public $sortColumn;

    public $delimiter;

    public $emailColumn;

    public $settings;

    public $dataSourceId;

    public $enabled;

    public $groupId;

    public $dateCreated;

    public $dateUpdated;

    public $results;

    /**
     * @return bool
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('sproutreports_reports');

        $this->query->select([
            'sproutreports_reports.dataSourceId',
            'sproutreports_reports.name',
            'sproutreports_reports.hasNameFormat',
            'sproutreports_reports.nameFormat',
            'sproutreports_reports.handle',
            'sproutreports_reports.description',
            'sproutreports_reports.allowHtml',
            'sproutreports_reports.sortOrder',
            'sproutreports_reports.sortColumn',
            'sproutreports_reports.delimiter',
            'sproutreports_reports.emailColumn',
            'sproutreports_reports.settings',
            'sproutreports_reports.groupId',
            'sproutreports_reports.enabled'
        ]);

        $this->query->innerJoin(DataSourceRecord::tableName().' sproutreports_datasources', '[[sproutreports_datasources.id]] = [[sproutreports_reports.dataSourceId]]');

        if ($this->emailColumn) {
            $this->query->andWhere(Db::parseParam(
                '[[sproutreports_reports.emailColumn]]', $this->emailColumn
            ));
        }

        if ($this->dataSourceId) {
            $this->query->andWhere(Db::parseParam(
                '[[sproutreports_reports.dataSourceId]]', $this->dataSourceId)
            );
        }

        if ($this->groupId) {
            $this->query->andWhere(Db::parseParam(
                '[[sproutreports_reports.groupId]]', $this->groupId)
            );
        }

        return parent::beforePrepare();
    }
}
