<?php

namespace barrelstrength\sproutbase\app\reports\visualizations;

use barrelstrength\sproutbase\app\reports\base\Visualization;
use Craft;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

class PieChartVisualization extends Visualization
{
    /**
     * @inheritdoc
     */

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Pie Chart');
    }

    /**
     * @param array $settings
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml(array $settings): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-reports/_components/visualizations/PieChart/settings', [
            'settings' => $settings
        ]);
    }

    /**
     * @param array $options
     *
     * @return string
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getVisualizationHtml(array $options = []): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-reports/_components/visualizations/PieChart/visualization', [
            'visualization' => $this,
            'options' => $options,
        ]);
    }
}