<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\controllers;

use barrelstrength\sproutbase\models\sproutreports\Report;
use barrelstrength\sproutbase\records\sproutreports\Report as ReportRecord;
use barrelstrength\sproutbase\SproutBase;
use Craft;

use craft\helpers\UrlHelper;
use craft\web\assets\cp\CpAsset;
use craft\web\Controller;

class ReportsController extends Controller
{
    public function actionIndex($dataSourceId = null)
    {
        $dataSource = SproutBase::$app->dataSources->getDataSourceById($dataSourceId);

        $reports = SproutBase::$app->reports->getReportsBySourceId($dataSourceId);

        return $this->renderTemplate('sprout-base/sproutreports/reports/index', [
            'groupId' => null,
            'reports' => $reports,
            'dataSource' => $dataSource
        ]);
    }

    public function actionResultsIndex(Report $report = null, int $reportId = null)
    {
        if (isset($report)) {
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

        throw new \HttpException(404, SproutBase::t('Report not found.'));
    }

    public function actionEditReport(string $dataSourceId, Report $report = null, int $reportId = null)
    {
        $reportModel = new Report();

        if (isset($report)) {
            $reportModel = $report;
        } elseif ($reportId != null) {
            $reportModel = SproutBase::$app->reports->getReport($reportId);
        }

        // This is for creating new report
        if ($dataSourceId != null) {
            $reportModel->dataSourceId = $dataSourceId;
        }

        $dataSource = $reportModel->getDataSource();

        $indexUrl = $dataSource->getUrl();
        // Make sure you navigate to the right plugin page after saving and breadcrumb
        if (Craft::$app->getPlugins()->getPlugin('sprout-reports')
            && Craft::$app->request->getSegment(1) == 'sprout-reports') {
            $indexUrl = UrlHelper::cpUrl('/sprout-reports/reports');
        }

        $groups = [];

        if (Craft::$app->getPlugins()->getPlugin('sprout-reports')) {
            $groups = \barrelstrength\sproutreports\SproutReports::$app->reportGroups->getAllReportGroups();
        }

        return $this->renderTemplate('sprout-base/sproutreports/reports/_edit', [
            'report' => $reportModel,
            'dataSource' => $dataSource,
            'indexUrl' => $indexUrl,
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
        $options = $request->getBodyParam('options');

        if ($reportId && $options) {
            $reportModel = SproutBase::$app->reports->getReport($reportId);

            if (!$reportModel) {
                throw new \Exception(SproutBase::t('No report exists with the id “{id}”', ['id' => $reportId]));
            }

            $reportModel->options = is_array($options) ? $options : [];

            if (SproutBase::$app->reports->saveReport($reportModel)) {
                Craft::$app->getSession()->setNotice(SproutBase::t('Query updated.'));

                return $this->redirectToPostedUrl($reportModel);
            }
        }

        // Encode back to object after validation for getResults method to recognize option object
        $reportModel->options = json_encode($reportModel->options);

        Craft::$app->getSession()->setError(SproutBase::t('Could not update report.'));

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
     */
    public function actionSaveReport()
    {
        $this->requirePostRequest();

        $report = SproutBase::$app->reports->prepareFromPost();

        if (!SproutBase::$app->reports->saveReport($report)) {
            Craft::$app->getSession()->setError(SproutBase::t('Couldn’t save report.'));

            // Send the report back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'report' => $report
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(SproutBase::t('Report saved.'));

        return $this->redirectToPostedUrl($report);
    }

    public function actionDeleteReport()
    {
        $this->requirePostRequest();

        $reportId = Craft::$app->getRequest()->getBodyParam('reportId');

        if ($record = ReportRecord::findOne($reportId)) {
            $record->delete();

            Craft::$app->getSession()->setNotice(SproutBase::t('Report deleted.'));

            return $this->redirectToPostedUrl($record);
        } else {
            throw new \Exception(SproutBase::t('Report not found.'));
        }
    }

    public function actionExportReport()
    {
        $reportId = Craft::$app->getRequest()->getParam('reportId');

        $report = SproutBase::$app->reports->getReport($reportId);

        $options = Craft::$app->getRequest()->getBodyParam('options');

        $options = count($options) ? $options : [];

        if ($report) {
            $dataSource = SproutBase::$app->dataSources->getDataSourceById($report->dataSourceId);

            if ($dataSource) {
                $date = date('Ymd-his');

                $filename = $report->name.'-'.$date;
                $labels = $dataSource->getDefaultLabels($report, $options);
                $values = $dataSource->getResults($report, $options);

                SproutBase::$app->exports->toCsv($values, $labels, $filename);
            }
        }
    }
}
