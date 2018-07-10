<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\controllers;

use barrelstrength\sproutbase\app\reports\base\DataSource;
use barrelstrength\sproutbase\app\reports\elements\Report;
use barrelstrength\sproutbase\app\reports\models\ReportGroup;
use barrelstrength\sproutbase\app\reports\records\Report as ReportRecord;
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
        $currentPluginHandle = Craft::$app->request->getSegment(1);

        $dataSources = [];

        if ($currentPluginHandle !== 'sprout-reports') {

            $dataSource = SproutBase::$app->dataSources->getDataSourceById($dataSourceId);

            // Update to match the multi-datasource syntax
            if ($dataSource) {
                $dataSources[get_class($dataSource)] = $dataSource;
            }

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
            /**
             * @var $dataSource DataSource
             */
            // Ignore the allowNew setting if we're displaying a Reports integration
            if ($dataSource AND (bool)$dataSource->allowNew() OR $currentPluginHandle !== 'sprout-reports') {
                $newReportOptions[] = [
                    'name' => $dataSource->getName(),
                    'url' => $dataSource->getUrl($dataSource->dataSourceId.'/new')
                ];
            }
        }

        return $this->renderTemplate('sprout-base-reports/reports/index', [
            'dataSources' => $dataSources,
            'groupId' => $groupId,
            'reports' => $reports,
            'newReportOptions' => $newReportOptions,
            'currentPluginHandle' => $currentPluginHandle
        ]);
    }

    /**
     * @param Report|null $report
     * @param int|null    $reportId
     *
     * @return \yii\web\Response
     * @throws \HttpException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionResultsIndex(Report $report = null, int $reportId = null)
    {
        $currentPluginHandle = Craft::$app->request->getSegment(1);

        if ($report === null) {
            $report = SproutBase::$app->reports->getReport($reportId);
        }

        if ($report) {
            $dataSource = $report->getDataSource();

            $labels = $dataSource->getDefaultLabels($report);

            $variables['reportIndexUrl'] = $dataSource->getUrl($report->groupId);

            if ($currentPluginHandle !== 'sprout-reports') {
                $variables['reportIndexUrl'] = $dataSource->getUrl($dataSource->dataSourceId);
            }

            $variables['dataSource'] = null;
            $variables['report'] = $report;
            $variables['values'] = [];
            $variables['reportId'] = $reportId;
            $variables['redirectUrl'] = Craft::$app->getRequest()->getSegment(1).'/reports/view/'.$reportId;

            if ($dataSource) {
                $values = $dataSource->getResults($report);

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
            return $this->renderTemplate('sprout-base-reports/results/index', $variables);
        }

        throw new \HttpException(404, Craft::t('sprout-base', 'Report not found.'));
    }

    /**
     * @param string      $dataSourceId
     * @param string      $dataSourceSlug
     * @param Report|null $report
     * @param int|null    $reportId
     *
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionEditReport(string $dataSourceId, Report $report = null, int $reportId = null)
    {
        $currentPluginHandle = Craft::$app->request->getSegment(1);

        $reportElement = new Report();
        $reportElement->enabled = 1;

        if ($report !== null) {
            $reportElement = $report;
        } elseif ($reportId !== null) {
            $reportElement = SproutBase::$app->reports->getReport($reportId);
        }

        // This is for creating new report
        if ($dataSourceId !== null) {
            $reportElement->dataSourceId = $dataSourceId;
        }

        $dataSource = $reportElement->getDataSource();

        $reportIndexUrl = $dataSource->getUrl($reportElement->groupId);

        if ($currentPluginHandle !== 'sprout-reports') {
            $reportIndexUrl = $dataSource->getUrl($dataSource->dataSourceId);
        }

        // Make sure you navigate to the right plugin page after saving and breadcrumb
        if (Craft::$app->request->getSegment(1) == 'sprout-reports') {
            $reportIndexUrl = UrlHelper::cpUrl('/sprout-reports/reports');
        }

        $groups = [];

        if (Craft::$app->getPlugins()->getPlugin('sprout-reports')) {
            $groups = SproutBase::$app->reportGroups->getAllReportGroups();
        }

        return $this->renderTemplate('sprout-base-reports/reports/_edit', [
            'report' => $reportElement,
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
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUpdateReport()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $reportElement = new Report();

        $reportId = $request->getBodyParam('reportId');
        $settings = $request->getBodyParam('settings');

        if ($reportId && $settings) {
            $reportElement = SproutBase::$app->reports->getReport($reportId);

            if (!$reportElement) {
                throw new \InvalidArgumentException(Craft::t('sprout-base', 'No report exists with the id “{id}”', ['id' => $reportId]));
            }

            $reportElement->settings = is_array($settings) ? $settings : [];

            if (SproutBase::$app->reports->saveReport($reportElement)) {
                Craft::$app->getSession()->setNotice(Craft::t('sprout-base', 'Query updated.'));

                return $this->redirectToPostedUrl($reportElement);
            }
        }

        // Encode back to object after validation for getResults method to recognize option object
        $reportElement->settings = json_encode($reportElement->settings);

        Craft::$app->getSession()->setError(Craft::t('sprout-base', 'Could not update report.'));

        // Send the report back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'report' => $reportElement
        ]);

        return null;
    }

    /**
     * Saves a report query to the database
     *
     * @return null|\yii\web\Response
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveReport()
    {
        $this->requirePostRequest();

        $report = $this->prepareFromPost();

        $session = Craft::$app->getSession();

        if ($session AND $report->validate()) {
            if (Craft::$app->getElements()->saveElement($report)) {
                Craft::$app->getSession()->setNotice(Craft::t('sprout-base', 'Report saved.'));

                return $this->redirectToPostedUrl($report);
            }
        }

        Craft::$app->getSession()->setError(Craft::t('sprout-base', 'Couldn’t save report.'));

        // Send the report back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'report' => $report
        ]);

        return null;
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
     *
     * @throws \yii\base\Exception
     */
    public function actionExportReport()
    {
        $reportId = Craft::$app->getRequest()->getParam('reportId');

        $report = SproutBase::$app->reports->getReport($reportId);

        $settings = Craft::$app->getRequest()->getBodyParam('settings');

        $settings = count($settings) ? $settings : [];

        if ($report) {
            $dataSource = SproutBase::$app->dataSources->getDataSourceById($report->dataSourceId);

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
     * @return Report
     * @throws \yii\base\Exception
     */
    public function prepareFromPost()
    {
        $request = Craft::$app->getRequest();

        $reportId = $request->getBodyParam('id');

        if ($reportId && is_numeric($reportId)) {
            $report = SproutBase::$app->reports->getReport($reportId);

            if (!$report) {
                $report->addError('id', Craft::t('sprout-base', 'Could not find a report with id {reportId}', [
                    'reportId' => $reportId
                ]));
            }
        } else {
            $report = new Report();
        }

        $settings = $request->getBodyParam('settings');

        $report->name = $request->getBodyParam('name');
        $report->hasNameFormat = $request->getBodyParam('hasNameFormat');
        $report->nameFormat = $request->getBodyParam('nameFormat');
        $report->handle = $request->getBodyParam('handle');
        $report->description = $request->getBodyParam('description');
        $report->settings = is_array($settings) ? $settings : [];
        $report->dataSourceId = $request->getBodyParam('dataSourceId');
        $report->enabled = $request->getBodyParam('enabled', false);
        $report->groupId = $request->getBodyParam('groupId', null);

        $dataSource = $report->getDataSource();

        $report->allowHtml = $request->getBodyParam('allowHtml', $dataSource->getDefaultAllowHtml());

        return $report;
    }
}
