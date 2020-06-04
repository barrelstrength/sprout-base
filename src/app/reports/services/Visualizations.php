<?php

namespace barrelstrength\sproutbase\app\reports\services;

use barrelstrength\sproutbase\app\reports\base\Visualization;
use barrelstrength\sproutbase\app\reports\visualizations\BarChartVisualization;
use barrelstrength\sproutbase\app\reports\visualizations\LineChartVisualization;
use barrelstrength\sproutbase\app\reports\visualizations\PieChartVisualization;
use barrelstrength\sproutbase\app\reports\visualizations\TimeChartVisualization;
use craft\base\Component;

/**
 *
 * @property array $aggregates
 * @property array $visualizations
 */
class Visualizations extends Component
{
    /**
     * Get the list of available visualizations
     *
     * @return array
     */
    public function getVisualizations(): array
    {
        /** @var Visualization[] $visualizationTypes */
        $visualizationTypes = [
            BarChartVisualization::class,
            LineChartVisualization::class,
            PieChartVisualization::class,
            TimeChartVisualization::class,
        ];

        $visualizations = [];

        foreach ($visualizationTypes as $class) {
            $visualizations[] = [
                'value' => $class,
                'label' => $class::displayName(),
                'chart' => new $class,
            ];
        }

        return $visualizations;
    }

    /**
     * Get the list of aggregate functions to use for aggregating visualization data
     */

    public function getAggregates(): array
    {
        $aggregates = [];
        $aggregates[] = ['label' => 'None', 'value' => ''];
        $aggregates[] = ['label' => 'Sum', 'value' => 'sum'];
        $aggregates[] = ['label' => 'Count', 'value' => 'count'];
        $aggregates[] = ['label' => 'Average', 'value' => 'average'];

        return $aggregates;
    }
}
