<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\controllers;

use barrelstrength\sproutbase\app\reports\base\DataSource;
use barrelstrength\sproutbase\app\reports\base\Visualization;
use barrelstrength\sproutbase\app\reports\elements\Report;
use barrelstrength\sproutbase\app\reports\models\ReportGroup;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\errors\ElementNotFoundException;
use craft\errors\MissingComponentException;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use craft\web\Request;
use Throwable;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ReportsController extends Controller
{
    /**
     * @param null $groupId
     *
     * @return Response
     * @throws Exception
     * @throws ForbiddenHttpException
     */
    public function actionReportsIndexTemplate($groupId = null): Response
    {
        $this->requirePermission('sprout:reports:viewReports');

        $dataSources = SproutBase::$app->dataSources->getInstalledDataSources();

        if ($groupId !== null) {
            $reports = SproutBase::$app->reports->getReportsByGroupId($groupId);
        } else {
            $reports = SproutBase::$app->reports->getAllReports();
        }

        $newReportOptions = [];
        $allowedDataSourceIds = SproutBase::$app->reports->getAllowedDataSourceIds();

        foreach ($dataSources as $dataSource) {

            if (!$dataSource->allowNew || !in_array($dataSource->id, $allowedDataSourceIds, true)) {
                continue;
            }

            $newReportOptions[] = [
                'name' => $dataSource::displayName(),
                'url' => UrlHelper::cpUrl('sprout/reports/'.$dataSource->id.'/new'),
            ];
        }

        $currentUser = Craft::$app->getUser()->getIdentity();

        $config = SproutBase::$app->config->getConfigByKey('reports');

        return $this->renderTemplate('sprout/reports/reports/index', [
            'dataSources' => $dataSources,
            'groupId' => $groupId,
            'reports' => $reports,
            'config' => $config,
            'newReportOptions' => $newReportOptions,
            'editReportsPermission' => $currentUser->can('sprout:reports:editReports'),
        ]);
    }

    /**
     * @param Report|null $report
     * @param int|null $reportId
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionResultsIndexTemplate(Report $report = null, int $reportId = null): Response
    {
        $this->requirePermission('sprout:reports:viewReports');

        if ($report === null) {
            $report = Craft::$app->elements->getElementById($reportId, Report::class);
        }

        if (!$report) {
            throw new NotFoundHttpException('Report not found.');
        }

        $dataSource = $report->getDataSource();

        if (!$dataSource) {
            throw new NotFoundHttpException('Data Source not found.');
        }

        $labels = $dataSource->getDefaultLabels($report);

        $values = $dataSource->getResults($report);

        if (empty($labels) && !empty($values)) {
            $firstItemInArray = reset($values);
            $labels = array_keys($firstItemInArray);
        }

        // Get the position of our sort column for the Data Table settings
        $sortColumnPosition = array_search($report->sortColumn, $labels, true);

        if (!is_int($sortColumnPosition)) {
            $sortColumnPosition = null;
        }

        $visualizationSettings = $report->getSetting('visualization');

        $visualizationType = $visualizationSettings['type'] ?? null;
        $visualization = class_exists($visualizationType) ? new $visualizationType() : null;

        if ($visualization instanceof Visualization) {
            $visualization->setSettings($visualizationSettings);
            $visualization->setLabels($labels);
            $visualization->setValues($values);
        } else {
            $visualization = null;
        }

        $currentUser = Craft::$app->getUser()->getIdentity();

        $config = SproutBase::$app->config->getConfigByKey('reports');

        return $this->renderTemplate('sprout/reports/results/index', [
            'report' => $report,
            'visualization' => $visualization,
            'dataSource' => $dataSource,
            'labels' => $labels,
            'values' => $values,
            'redirectUrl' => 'sprout/reports/view/'.$reportId,
            'config' => $config,
            'editReportsPermission' => $currentUser->can('sprout:reports:editReports'),
            'settings' => SproutBase::$app->settings->getSettingsByKey('reports'),
            'sortColumnPosition' => $sortColumnPosition,
        ]);
    }

    /**
     * @param string $dataSourceId
     * @param Report|null $report
     * @param int|null $reportId
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws MissingComponentException
     */
    public function actionEditReportTemplate(string $dataSourceId = null, Report $report = null, int $reportId = null): Response
    {
        $this->requirePermission('sprout:reports:editReports');

        $reportElement = new Report();
        $reportElement->enabled = 1;

        if ($report !== null) {
            $reportElement = $report;
        } elseif ($reportId !== null) {
            $reportElement = Craft::$app->elements->getElementById($reportId, Report::class);
        }

        // This is for creating new report
        if ($dataSourceId !== null) {
            $reportElement->dataSourceId = $dataSourceId;
        }

        $dataSource = $reportElement->getDataSource();

        if (!$dataSource) {
            throw new NotFoundHttpException('Data Source not found.');
        }

        if ($message = $this->processEditionChecks($reportElement)) {
            Craft::$app->getSession()->setNotice($message);

            return $this->redirect(UrlHelper::cpUrl('sprout/reports'));
        }

        $groups = SproutBase::$app->reportGroups->getReportGroups();

        $emailColumnOptions = [
            [
                'label' => 'None',
                'value' => '',
            ],
            [
                'label' => 'Email (email)',
                'value' => 'email',
            ],
            [
                'optgroup' => 'Custom',
            ],
        ];

        if (!in_array($reportElement->emailColumn, ['', 'email'], true)) {
            $emailColumnOptions[] = [
                'label' => $reportElement->emailColumn,
                'value' => $reportElement->emailColumn,
            ];
        }

        $emailColumnOptions[] = [
            'label' => 'Add custom',
            'value' => 'custom',
        ];

        $delimiterOptions = [
            [
                'label' => Craft::t('sprout', 'Comma'),
                'value' => $reportElement::DELIMITER_COMMA,
            ],
            [
                'label' => Craft::t('sprout', 'Semi-colon'),
                'value' => $reportElement::DELIMITER_SEMICOLON,
            ],
            [
                'label' => Craft::t('sprout', 'Tab'),
                'value' => $reportElement::DELIMITER_TAB,
            ],
        ];

        $visualizations = SproutBase::$app->visualizations->getVisualizations();
        $visualizationOptions = array_merge([['value' => '', 'label' => 'None']], $visualizations);

        // @todo - review visualization implementation of settings here
        //         conflicts with $settings variable passed to template below
        $settings = $reportElement->getSettings();

        //determine if the report settings have the basic visualization settings
        if ($settings === null || array_key_exists('visualization', $settings) === false) {
            $settings['visualization'] = [
                'type' => '',
                'labelColumn' => '',
                'dataColumns' => [''],
                'aggregate' => '',
                'decimals' => 0,
            ];
        }

        //determine if the report settings have the basic visualization settings
        if (array_key_exists('labelColumn', $settings['visualization']) === false) {
            $settings['visualization']['labelColumn'] = '';
        }

        //determine if the report settings have the basic visualization settings
        if (array_key_exists('dataColumns', $settings['visualization']) === false) {
            $settings['visualization']['dataColumns'] = [''];
        }

        //determine if the report settings have the basic visualization settings
        if (array_key_exists('aggregate', $settings['visualization']) === false) {
            $settings['visualization']['aggregate'] = '';
        }

        //determine if the report settings have the basic visualization settings
        if (array_key_exists('decimals', $settings['visualization']) === false) {
            $settings['visualization']['decimals'] = 0;
        }

        $currentUser = Craft::$app->getUser()->getIdentity();

        $config = SproutBase::$app->config->getConfigByKey('reports');

        return $this->renderTemplate('sprout/reports/reports/_edit', [
            'report' => $reportElement,
            'dataSource' => $dataSource,
            'groups' => $groups,
            'continueEditingUrl' => UrlHelper::cpUrl('sprout/reports/'.$dataSourceId.'/edit/{id}'),
            'editReportsPermission' => $currentUser->can('sprout:reports:editReports'),
            'config' => $config,
            'emailColumnOptions' => $emailColumnOptions,
            'delimiterOptions' => $delimiterOptions,
            'settings' => $settings,
            'reportsSettings' => SproutBase::$app->settings->getSettingsByKey('reports'),
            'visualizationOptions' => $visualizationOptions,
            'visualizationTypes' => $visualizations,
        ]);
    }

    /**
     * Saves a report query to the database
     *
     * @return Response|null
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws MissingComponentException
     * @throws BadRequestHttpException
     */
    public function actionUpdateReport()
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:reports:editReports');

        $request = Craft::$app->getRequest();

        $reportId = $request->getBodyParam('reportId');
        $settings = $request->getBodyParam('settings');

        /** @var Report $report */
        $report = Craft::$app->elements->getElementById($reportId, Report::class);
        $report->setSettings($settings);

        if (!$report) {
            throw new NotFoundHttpException('No report exists with the ID: '.$reportId);
        }

        if (!SproutBase::$app->reports->saveReport($report)) {
            Craft::$app->getSession()->setError(Craft::t('sprout', 'Could not update report.'));

            // Send the report back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'report' => $report,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Query updated.'));

        return $this->redirectToPostedUrl($report);
    }

    /**
     * Saves a report query to the database
     *
     * @return null|Response
     * @throws Throwable
     * @throws ElementNotFoundException
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionSaveReport()
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:reports:editReports');

        $report = $this->prepareFromPost();

        SproutBase::$app->reports->validateSettings($report);

        if ($report->hasErrors() || !Craft::$app->getElements()->saveElement($report)) {
            Craft::$app->getSession()->setError(Craft::t('sprout', 'Couldn’t save report.'));

            // Send the report back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'report' => $report,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Report saved.'));

        return $this->redirectToPostedUrl($report);
    }

    /**
     * Deletes a Report
     *
     * @return Response
     * @throws \Exception
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionDeleteReport(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:reports:editReports');

        $reportId = Craft::$app->getRequest()->getBodyParam('id');
        $report = Craft::$app->getElements()->getElementById($reportId);

        if (!$report || !Craft::$app->getElements()->deleteElement($report, true)) {
            throw new NotFoundHttpException('Unable to delete report.');
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Report deleted.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * Saves a Report Group
     *
     * @return Response
     * @throws MissingComponentException
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionSaveGroup(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:reports:editReports');

        $request = Craft::$app->getRequest();

        $groupName = $request->getBodyParam('name');

        $group = new ReportGroup();
        $group->id = $request->getBodyParam('id');
        $group->name = $groupName;

        if (SproutBase::$app->reportGroups->saveGroup($group)) {

            Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Report group saved.'));

            return $this->asJson([
                'success' => true,
                'group' => $group->getAttributes(),
            ]);
        }

        return $this->asJson([
            'errors' => $group->getErrors(),
        ]);
    }

    /**
     * Deletes a Report Group
     *
     * @return Response
     * @throws \Exception
     * @throws Throwable
     * @throws StaleObjectException
     * @throws BadRequestHttpException
     */
    public function actionDeleteGroup(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('sprout:reports:editReports');

        $groupId = Craft::$app->getRequest()->getBodyParam('id');
        $success = SproutBase::$app->reportGroups->deleteGroup($groupId);

        Craft::$app->getSession()->setNotice(Craft::t('sprout', 'Group deleted.'));

        return $this->asJson([
            'success' => $success,
        ]);
    }

    /**
     * Export a Report
     *
     * @throws Exception
     */
    public function actionExportReport()
    {
        $this->requirePermission('sprout:reports:viewReports');

        $reportId = Craft::$app->getRequest()->getParam('reportId');

        /** @var Report $report */
        $report = Craft::$app->elements->getElementById($reportId, Report::class);

        if (!$report) {
            throw new ElementNotFoundException('Report not found');
        }
        
        $dataSource = SproutBase::$app->dataSources->getDataSourceById($report->dataSourceId);

        if (!$dataSource) {
            throw new NotFoundHttpException('Report not found');
        }

        $date = date('Ymd-his');

        // Name the report using the $report toString method that
        // will check both nameFormat and name
        $filename = $report.'-'.$date;

        $dataSource->isExport = true;
        $labels = $dataSource->getDefaultLabels($report);
        $values = $dataSource->getResults($report);

        SproutBase::$app->exports->toCsv($values, $labels, $filename, $report->delimiter);
    }

    /**
     * Returns a report model populated from saved/POSTed data
     *
     * @return Report
     * @throws Exception
     */
    public function prepareFromPost(): Report
    {
        $request = Craft::$app->getRequest();

        $reportId = $request->getBodyParam('id');

        if ($reportId) {
            $report = Craft::$app->elements->getElementById($reportId, Report::class);

            if (!$report) {
                $report->addError('id', Craft::t('sprout', 'Could not find a report with id {reportId}', [
                    'reportId' => $reportId,
                ]));
            }
        } else {
            $report = new Report();
        }

        $settings = $request->getBodyParam('settings');
        $settings['visualization'] = $this->getVisualizationSettings($request);

        $report->name = $request->getBodyParam('name');
        $report->hasNameFormat = $request->getBodyParam('hasNameFormat');
        $report->nameFormat = $request->getBodyParam('nameFormat');
        $report->handle = $request->getBodyParam('handle');
        $report->description = $request->getBodyParam('description');
        $report->setSettings($settings);
        $report->dataSourceId = $request->getBodyParam('dataSourceId');
        $report->enabled = $request->getBodyParam('enabled', false);
        $report->groupId = $request->getBodyParam('groupId');
        $report->sortOrder = $request->getBodyParam('sortOrder');
        $report->sortColumn = $request->getBodyParam('sortColumn');
        $report->delimiter = $request->getBodyParam('delimiter');

        $dataSource = $report->getDataSource();

        if (!$dataSource) {
            throw new NotFoundHttpException('Date Source not found.');
        }

        $report->emailColumn = !$dataSource->isEmailColumnEditable() ? $dataSource->getDefaultEmailColumn() : $request->getBodyParam('emailColumn');

        $report->allowHtml = $request->getBodyParam('allowHtml', $dataSource->getDefaultAllowHtml());

        return $report;
    }

    private function getVisualizationSettings(Request $request): array
    {
        $visualizationType = $request->getBodyParam('visualizationType');
        $visualizationSettings = $request->getBodyParam('visualizations.'.$visualizationType);
        $visualizationSettings['type'] = $visualizationType;

        return $visualizationSettings;
    }

    /**
     * Returns null on success and an error message on failure
     *
     * @param Report $report
     *
     * @return string|null
     */
    private function processEditionChecks(Report $report)
    {
        $isPro = SproutBase::$app->config->isEdition('reports', Config::EDITION_PRO);

        // No changes if Pro or if editing an existing report
        if ($isPro || $report->id) {
            return null;
        }

        /** @var DataSource $dataSource */
        $dataSource = $report->getDataSource();
        $dataSourceId = $dataSource->id;

        $allowedDataSourceIds = SproutBase::$app->reports->getAllowedDataSourceIds();

        if (!in_array($dataSourceId, $allowedDataSourceIds, false)) {
            return Craft::t('sprout', 'Upgrade to Sprout Reports PRO to use Custom Reports and Data Source integrations.');
        }

        return null;
    }
}
