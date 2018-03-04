<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\controllers;

use barrelstrength\sproutbase\models\sproutreports\Report as ReportModel;
use barrelstrength\sproutbase\models\sproutreports\Report;
use barrelstrength\sproutbase\models\sproutreports\ReportGroup;
use barrelstrength\sproutbase\records\sproutreports\Report as ReportRecord;
use barrelstrength\sproutbase\SproutBase;
use Craft;

use craft\helpers\UrlHelper;
use craft\web\assets\cp\CpAsset;
use craft\web\Controller;
use yii\web\NotFoundHttpException;

class ReportsController extends Controller
{
    /**
     * @param null $dataSourceId
     * @param null $groupId
     *
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionIndex($dataSourceId = null, $groupId = null)
    {
        $reportContext = 'sprout-reports';

        // If a type is provided we have an integration
        if ($dataSourceId !== null) {
            $reportContext = 'sprout-integration';

            $dataSource = SproutBase::$app->dataSources->getDataSourceById($dataSourceId);

            // Update to match the multi-datasource syntax
            $dataSources[$dataSource->getDataSourceSlug()] = $dataSource;

            $reports = SproutBase::$app->reports->getReportsBySourceId($dataSourceId);
        } else {

            $dataSources = SproutBase::$app->dataSources->getAllDataSources();

            if ($groupId !== null) {
                $reports = SproutBase::$app->reports->getReportsByGroupId($groupId);
            } else {
                $reports = SproutBase::$app->reports->getAllReports();
            }
        }

        $newReportOptions = [];

        foreach ($dataSources as $dataSource) {
            if ($dataSource AND (bool)$dataSource->allowNew()) {
                $newReportOptions[] = [
                    'name' => $dataSource->getName(),
                    'url' => $dataSource->getUrl('/new')
                ];
            }
        }

        return $this->renderTemplate('sprout-base/sproutreports/reports/index', [
            'dataSources' => $dataSources,
            'groupId' => $groupId,
            'reports' => $reports,
            'newReportOptions' => $newReportOptions,
            'reportContext' => $reportContext
        ]);
    }

    /**
     * @param Report|null $report
     * @param int|null    $reportId
     *
     * @return \yii\web\Response
     * @throws \HttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionResultsIndex(Report $report = null, int $reportId = null)
    {
        if ($report !== null) {
            $reportModel = $report;
        } else {
            $reportModel = SproutBase::$app->reports->getReport($reportId);
        }

        if ($reportModel) {
            $dataSource = $reportModel->getDataSource();

            $labels = $dataSource->getDefaultLabels($reportModel);

            $variables['dataSource'] = null;
            $variables['report'] = $reportModel;
            $variables['values'] = [];
            $variables['reportId'] = $reportId;
            $variables['redirectUrl'] = Craft::$app->getRequest()->getSegment(1).'/reports/view/'.$reportId;

            if ($dataSource) {
                $values = $dataSource->getResults($reportModel);

                if (empty($labels) && !empty($values)) {
                    $firstItemInArray = reset($values);
                    $labels = array_keys($firstItemInArray);
                }

                $variables['labels'] = $labels;
                $variables['values'] = $values;
                $variables['dataSource'] = $dataSource;
            }

            $this->getView()->registerAssetBundle(CpAsset::class);

            // @todo Hand off to the export service when a blank page and 404 issues are sorted out
            return $this->renderTemplate('sprout-base/sproutreports/results/index', $variables);
        }

        throw new \HttpException(404, Craft::t('sprout-base', 'Report not found.'));
    }

    /**
     * @param string      $dataSourceId
     * @param Report|null $report
     * @param int|null    $reportId
     *
     * @return \yii\web\Response
     */
    public function actionEditReport(string $dataSourceId, string $dataSourceSlug, Report $report = null, int $reportId = null)
    {
        $reportModel = new Report();

        if ($report !== null) {
            $reportModel = $report;
        } elseif ($reportId !== null) {
            $reportModel = SproutBase::$app->reports->getReport($reportId);
        }

        // This is for creating new report
        if ($dataSourceId !== null) {
            $reportModel->dataSourceId = $dataSourceId;
        }

        $dataSource = $reportModel->getDataSource();

        $reportIndexUrl = $dataSource->getUrl();

        // Make sure you navigate to the right plugin page after saving and breadcrumb
        if (Craft::$app->request->getSegment(1) == 'sprout-reports') {
            $reportIndexUrl = UrlHelper::cpUrl('/sprout-reports/reports');
        }

        $groups = [];

        if (Craft::$app->getPlugins()->getPlugin('sprout-reports')) {
            $groups = SproutBase::$app->reportGroups->getAllReportGroups();
        }

        return $this->renderTemplate('sprout-base/sproutreports/reports/_edit', [
            'report' => $reportModel,
            'dataSource' => $dataSource,
            'reportIndexUrl' => $reportIndexUrl,
            'groups' => $groups,
            'continueEditingUrl' => $dataSource->getUrl().'/edit/{id}'
        ]);
    }

    /**
     * Saves a report query to the database
     *
     * @return null|\yii\web\Response
     * @throws \Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUpdateReport()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $reportModel = new Report();

        $reportId = $request->getBodyParam('reportId');
        $settings = $request->getBodyParam('settings');

        if ($reportId && $settings) {
            $reportModel = SproutBase::$app->reports->getReport($reportId);

            if (!$reportModel) {
                throw new \InvalidArgumentException(Craft::t('sprout-base', 'No report exists with the id “{id}”', ['id' => $reportId]));
            }

            $reportModel->settings = is_array($settings) ? $settings : [];

            if (SproutBase::$app->reports->saveReport($reportModel)) {
                Craft::$app->getSession()->setNotice(Craft::t('sprout-base', 'Query updated.'));

                return $this->redirectToPostedUrl($reportModel);
            }
        }

        // Encode back to object after validation for getResults method to recognize option object
        $reportModel->settings = json_encode($reportModel->settings);

        Craft::$app->getSession()->setError(Craft::t('sprout-base', 'Could not update report.'));

        // Send the report back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'report' => $reportModel
        ]);

        return null;
    }

    /**
     * Saves a report query to the database
     *
     * @return null|\yii\web\Response
     * @throws \Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveReport()
    {
        $this->requirePostRequest();

        $report = $this->prepareFromPost();

        if (!SproutBase::$app->reports->saveReport($report)) {
            Craft::$app->getSession()->setError(Craft::t('sprout-base', 'Couldn’t save report.'));

            // Send the report back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'report' => $report
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-base', 'Report saved.'));

        return $this->redirectToPostedUrl($report);
    }

    /**
     * Deletes a Report
     *
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteReport()
    {
        $this->requirePostRequest();

        $reportId = Craft::$app->getRequest()->getBodyParam('id');

        if ($record = ReportRecord::findOne($reportId)) {
            $record->delete();

            Craft::$app->getSession()->setNotice(Craft::t('sprout-base', 'Report deleted.'));

            return $this->redirectToPostedUrl($record);
        } else {
            throw new NotFoundHttpException(Craft::t('sprout-base', 'Report not found.'));
        }
    }

    /**
     * Saves a Report Group
     *
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveGroup()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $groupName = $request->getBodyParam('name');

        $group = new ReportGroup();
        $group->id = $request->getBodyParam('id');
        $group->name = $groupName;

        if (SproutBase::$app->reportGroups->saveGroup($group)) {

            Craft::$app->getSession()->setNotice(Craft::t('sprout-base', 'Report group saved.'));

            return $this->asJson([
                'success' => true,
                'group' => $group->getAttributes(),
            ]);
        } else {
            return $this->asJson([
                'errors' => $group->getErrors(),
            ]);
        }
    }

    /**
     * Deletes a Report Group
     *
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteGroup()
    {
        $this->requirePostRequest();

        $groupId = Craft::$app->getRequest()->getBodyParam('id');
        $success = SproutBase::$app->reportGroups->deleteGroup($groupId);

        Craft::$app->getSession()->setNotice(Craft::t('sprout-base', 'Group deleted..'));

        return $this->asJson([
            'success' => $success,
        ]);
    }

    /**
     * Export a Report
     */
    public function actionExportReport()
    {
        $reportId = Craft::$app->getRequest()->getParam('reportId');

        $report = SproutBase::$app->reports->getReport($reportId);

        $settings = Craft::$app->getRequest()->getBodyParam('settings');

        $settings = count($settings) ? $settings : [];

        if ($report) {
            $dataSource = SproutBase::$app->dataSources->getDataSourceById($report->type);

            if ($dataSource) {
                $date = date('Ymd-his');

                $filename = $report->name.'-'.$date;
                $labels = $dataSource->getDefaultLabels($report, $settings);
                $values = $dataSource->getResults($report, $settings);

                SproutBase::$app->exports->toCsv($values, $labels, $filename);
            }
        }
    }

    /**
     * Returns a report model populated from saved/POSTed data
     *
     * @return ReportModel
     */
    public function prepareFromPost()
    {
        $request = Craft::$app->getRequest();

        $reportId = $request->getBodyParam('id');

        if ($reportId && is_numeric($reportId)) {
            $instance = SproutBase::$app->reports->getReport($reportId);

            if (!$instance) {
                $instance->addError('id', Craft::t('Could not find a report with id {reportId}', compact('reportId')));
            }
        } else {
            $instance = new ReportModel();
        }

        $settings = $request->getBodyParam('settings');

        $instance->name = $request->getBodyParam('name');
        $instance->nameFormat = $request->getBodyParam('nameFormat');
        $instance->handle = $request->getBodyParam('handle');
        $instance->description = $request->getBodyParam('description');
        $instance->settings = is_array($settings) ? $settings : [];
        $instance->dataSourceId = $request->getBodyParam('dataSourceId');
        $instance->enabled = $request->getBodyParam('enabled');
        $instance->groupId = $request->getBodyParam('groupId', null);

        $dataSource = $instance->getDataSource();

        $instance->allowHtml = $request->getBodyParam('allowHtml', $dataSource->getDefaultAllowHtml());

        return $instance;
    }
}
