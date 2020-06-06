<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\integrations\sproutreports\datasources;

use barrelstrength\sproutbase\app\reports\base\DataSource;
use barrelstrength\sproutbase\app\reports\elements\Report;
use barrelstrength\sproutbase\app\forms\elements\Form;
use barrelstrength\sproutbase\app\forms\records\Form as FormRecord;
use barrelstrength\sproutbase\app\forms\records\Integration as IntegrationRecord;
use barrelstrength\sproutbase\app\forms\records\IntegrationLog as IntegrationLogRecord;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\db\Query;
use craft\helpers\DateTimeHelper;
use DateTime;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class LogDataSource
 *
 * @package barrelstrength\sproutforms\integrations\sproutreports\datasources
 */
class IntegrationLogDataSource extends DataSource
{
    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Integration Log (Sprout Forms)');
    }

    /**
     * @return null|string
     */
    public function getDescription(): string
    {
        return Craft::t('sprout', 'Query form entry integrations results');
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function getResults(Report $report, array $settings = []): array
    {
        $startDate = null;
        $endDate = null;
        $formId = null;

        $startEndDate = $report->getStartEndDate();
        $startDate = $startEndDate->getStartDate();
        $endDate = $startEndDate->getEndDate();

        $rows = [];

        $formId = $report->getSetting('formId');

        $query = new Query();

        $formQuery = $query
            ->select('log.id id, log.dateCreated dateCreated, log.dateUpdated dateUpdated, log.entryId entryId, integrations.name integrationName, forms.name formName, log.message message, log.success success, log.status status')
            ->from(IntegrationLogRecord::tableName().' log')
            ->innerJoin(IntegrationRecord::tableName().' integrations', '[[log.integrationId]] = [[integrations.id]]')
            ->innerJoin(FormRecord::tableName().' forms', '[[integrations.formId]] = [[forms.id]]');

        if ($formId != '*') {
            $formQuery->andWhere(['[[integrations.formId]]' => $formId]);
        }

        if ($startDate && $endDate) {
            $formQuery->andWhere('[[log.dateCreated]] > :startDate', [
                ':startDate' => $startDate->format('Y-m-d H:i:s')
            ]);
            $formQuery->andWhere('[[log.dateCreated]] < :endDate', [
                ':endDate' => $endDate->format('Y-m-d H:i:s')
            ]);
        }

        $results = $formQuery->all();

        if (!$results) {
            return $rows;
        }

        foreach ($results as $key => $result) {
            $message = $result['message'];

            if (strlen($result['message']) > 255) {
                $message = substr($result['message'], 0, 255).' ...';
            }

            $rows[$key]['id'] = $result['id'];
            $rows[$key]['entryId'] = $result['entryId'];
            $rows[$key]['formName'] = $result['formName'];
            $rows[$key]['integrationName'] = $result['integrationName'];
            $rows[$key]['message'] = $message;
            $rows[$key]['status'] = $result['status'];
            $rows[$key]['success'] = $result['success'] ? 'true' : 'false';
            $rows[$key]['dateCreated'] = $result['dateCreated'];
            $rows[$key]['dateUpdated'] = $result['dateUpdated'];
        }

        return $rows;
    }

    /**
     * @inheritDoc
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml(array $settings = [])
    {
        /** @var Form[] $forms */
        $forms = Form::find()->limit(null)->orderBy('name')->all();

        if (empty($settings)) {
            $settings = (array)$this->report->getSettings();
        }

        $formOptions[] = ['label' => 'All', 'value' => '*'];

        foreach ($forms as $form) {
            $formOptions[] = [
                'label' => $form->name,
                'value' => $form->id
            ];
        }

        // @todo Determine sensible default start and end date based on Order data
        $defaultStartDate = null;
        $defaultEndDate = null;

        if (count($settings)) {
            if (isset($settings['startDate'])) {
                $startDateValue = (array)$settings['startDate'];

                $settings['startDate'] = DateTimeHelper::toDateTime($startDateValue);
            }

            if (isset($settings['endDate'])) {
                $endDateValue = (array)$settings['endDate'];

                $settings['endDate'] = DateTimeHelper::toDateTime($endDateValue);
            }
        }

        $dateRanges = SproutBase::$app->reports->getDateRanges(false);

        return Craft::$app->getView()->renderTemplate('sprout-base-forms/_integrations/sproutreports/datasources/IntegrationLogDataSource/settings', [
            'formOptions' => $formOptions,
            'defaultStartDate' => new DateTime($defaultStartDate),
            'defaultEndDate' => new DateTime($defaultEndDate),
            'dateRanges' => $dateRanges,
            'options' => $settings
        ]);
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function prepSettings(array $settings)
    {
        // Convert date strings to DateTime
        $settings['startDate'] = DateTimeHelper::toDateTime($settings['startDate']) ?: null;
        $settings['endDate'] = DateTimeHelper::toDateTime($settings['endDate']) ?: null;

        return $settings;
    }
}