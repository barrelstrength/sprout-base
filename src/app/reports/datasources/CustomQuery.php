<?php

namespace barrelstrength\sproutbase\app\reports\datasources;

use barrelstrength\sproutbase\app\reports\base\DataSource;
use barrelstrength\sproutbase\app\reports\elements\Report;
use Craft;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 *
 * @property string $description
 */
class CustomQuery extends DataSource
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Custom Query');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Create reports using a custom database query');
    }

    public function isAllowHtmlEditable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getResults(Report $report, array $settings = []): array
    {
        $query = $report->getSetting('query');

        $result = [];

        try {
            $result = Craft::$app->getDb()->createCommand($query)->queryAll();
        } catch (Exception $e) {
            $report->setResultsError($e->getMessage());
        }

        return $result;
    }

    /**
     * @param array $settings
     *
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml(array $settings = [])
    {
        $settingsErrors = $this->report->getErrors('settings');
        $settingsErrors = array_shift($settingsErrors);

        return Craft::$app->getView()->renderTemplate('sprout/reports/_components/datasources/CustomQuery/settings', [
            'settings' => count($settings) ? $settings : $this->report->getSettings(),
            'errors' => $settingsErrors,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function validateSettings(array $settings = [], array &$errors = []): bool
    {
        if (empty($settings['query'])) {
            $errors['query'][] = Craft::t('sprout', 'Query cannot be blank.');

            return false;
        }

        return true;
    }
}
