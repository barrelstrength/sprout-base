<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.github.io/license/
 */

namespace barrelstrength\sproutbase\app\reports\widgets;

use barrelstrength\sproutbase\app\reports\elements\Report;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Widget;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use function json_decode;

/**
 *
 * @property string|false $bodyHtml
 * @property null|string $settingsHtml
 * @property string $title
 */
class Visualization extends Widget
{
    /**
     * string The reportId of the report to be displayed
     */
    public $reportId = 0;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Sprout Report Chart');
    }

    /**
     * @inheritdoc
     */
    public static function icon()
    {
        return Craft::getAlias('@app/icons/clock.svg');
    }

    /**
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout/reports/_components/widgets/Visualizations/settings.twig',
            [
                'widget' => $this,
                'reports' => SproutBase::$app->reports->getAllReports(),
                'reportId' => $this->reportId,
            ]);
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        $report = Craft::$app->elements->getElementById($this->reportId, Report::class);
        if ($report) {
            $title = $report->name;
        } else {
            $title = Craft::t('sprout', 'Sprout Report Chart');
        }

        return $title;
    }

    /**
     * @return false|string
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getBodyHtml()
    {
        $dataSource = null;

        $report = Craft::$app->elements->getElementById($this->reportId, Report::class);

        if ($report) {
            $dataSource = $report->getDataSource();
        }

        $labels = $dataSource->getDefaultLabels($report);
        $values = $dataSource->getResults($report);

        if (empty($labels) && !empty($values)) {
            $firstItemInArray = reset($values);
            $labels = array_keys($firstItemInArray);
        }

        $settings = json_decode($report->settings, true);

        $visualization = false;

        if (array_key_exists('visualization', $settings)) {
            $visualization = new $settings['visualization']['type'];
            $visualization->setSettings($settings['visualization']);
            $visualization->setLabels($labels);
            $visualization->setValues($values);
        }

        return Craft::$app->getView()->renderTemplate('sprout/reports/_components/widgets/Visualizations/body', [
            'title' => 'report title',
            'visualization' => $visualization,
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['reportId'], 'number', 'integerOnly' => true];

        return $rules;
    }

}
