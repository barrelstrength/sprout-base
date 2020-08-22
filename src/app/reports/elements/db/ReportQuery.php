<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\elements\db;

use barrelstrength\sproutbase\app\reports\records\DataSource as DataSourceRecord;
use barrelstrength\sproutbase\app\reports\records\Report as ReportRecord;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\SproutBase;
use craft\db\Query;
use craft\elements\db\ElementQuery;
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
        $this->joinElementTable('sprout_reports');

        $this->query->select([
            'sprout_reports.dataSourceId',
            'sprout_reports.name',
            'sprout_reports.hasNameFormat',
            'sprout_reports.nameFormat',
            'sprout_reports.handle',
            'sprout_reports.description',
            'sprout_reports.allowHtml',
            'sprout_reports.sortOrder',
            'sprout_reports.sortColumn',
            'sprout_reports.delimiter',
            'sprout_reports.emailColumn',
            'sprout_reports.settings',
            'sprout_reports.groupId',
            'sprout_reports.enabled',
        ]);

        $this->query->innerJoin(DataSourceRecord::tableName().' sprout_data_sources', '[[sprout_data_sources.id]] = [[sprout_reports.dataSourceId]]');

        if ($this->groupId) {
            $this->query->andWhere(Db::parseParam(
                '[[sprout_reports.groupId]]', $this->groupId)
            );
        }

        if ($this->emailColumn) {
            $this->query->andWhere(Db::parseParam(
                '[[sprout_reports.emailColumn]]', $this->emailColumn
            ));
        }

        if ($this->dataSourceId) {
            $this->query->andWhere(Db::parseParam(
                '[[sprout_reports.dataSourceId]]', $this->dataSourceId)
            );
        }

        $this->modifyQueryForEditions();

        return parent::beforePrepare();
    }

    public function modifyQueryForEditions()
    {
        $isPro = SproutBase::$app->config->isEdition('reports', Config::EDITION_PRO);

        if (!$isPro) {
            $allowedDataSourceIds = SproutBase::$app->dataSources->getAllowedDataSourceIds();
            $defaultDataSourceIds = SproutBase::$app->dataSources->getDefaultDataSourceIds();

            // Restrict the query to Data Sources from enabled modules
            $dataSourceIdsCondition = [
                'in', '[[sprout_reports.dataSourceId]]', $allowedDataSourceIds,
            ];
            $this->query->andWhere($dataSourceIdsCondition);

            $totalAdditional = (new Query())
                ->select('*')
                ->from(ReportRecord::tableName())
                ->where([
                    'not', Db::parseParam('[[dataSourceId]]', $defaultDataSourceIds),
                ])
                ->count();

            // Restrict the query to return only 3 default Reports and any number of
            // default reports from other modules that were allowed to create reports
            $this->query->limit(3 + $totalAdditional);
        }
    }
}
