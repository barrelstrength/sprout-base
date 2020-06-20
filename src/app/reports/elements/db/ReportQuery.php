<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\elements\db;

use barrelstrength\sproutbase\app\reports\records\DataSource as DataSourceRecord;
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
            'sproutreports_reports.enabled',
        ]);

        $this->query->innerJoin(DataSourceRecord::tableName().' sproutreports_datasources', '[[sproutreports_datasources.id]] = [[sproutreports_reports.dataSourceId]]');

        if ($this->groupId) {
            $this->query->andWhere(Db::parseParam(
                '[[sproutreports_reports.groupId]]', $this->groupId)
            );
        }

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

        $this->modifyQueryForEditions();

        return parent::beforePrepare();
    }

    public function modifyQueryForEditions()
    {
        $isPro = SproutBase::$app->config->isEdition('reports', Config::EDITION_PRO);

        if (!$isPro) {
            $allowedDataSourceIds = SproutBase::$app->reports->getAllowedDataSourceIds();
            $defaultDataSourceIds = SproutBase::$app->reports->getDefaultDataSourceIds();

            // Restrict the query to Data Sources from enabled modules
            $dataSourceIdsCondition = [
                'in', '[[sproutreports_reports.dataSourceId]]', $allowedDataSourceIds,
            ];
            $this->query->andWhere($dataSourceIdsCondition);

            $totalAdditional = (new Query())
                ->select('*')
                ->from('{{%sproutreports_reports}}')
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
