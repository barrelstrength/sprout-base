<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services\sproutreports;

use League\Csv\Writer;
use yii\base\Component;

class Exports extends Component
{
    /**
     * @param array $values
     * @param array $labels
     * @param array $variables
     *
     * @return string
     */
    public function toHtml(array &$values, array $labels = [], array $variables = [])
    {
        // @todo - reconsider this logic
        if (empty($labels) && !empty($values)) {
            $arrayValues = array_values($values);
            $firstRowOfArray = array_shift($arrayValues);

            $labels = array_keys($firstRowOfArray);
        }

        $variables['labels'] = $labels;
        $variables['values'] = $values;

        return \Craft::$app->getView()->renderTemplate('sprout-base/results/index', $variables);
    }

    /**
     * @param array $values
     *
     * @throws \Exception
     * @return string
     */
    public function toJson(array &$values)
    {
        $json = json_encode($values);

        if (json_last_error()) {
            throw new \Exception(json_last_error_msg());
        }

        return $json;
    }

    /**
     * Takes an array of values and options labels and creates a downloadable CSV file
     *
     * @param array  $values
     * @param array  $labels
     * @param string $filename
     */
    public function toCsv(array &$values, array $labels = [], $filename = 'export.csv')
    {
        $filename = str_replace('.csv', '', $filename).'.csv';

        if (empty($labels) && !empty($values)) {
            $arrayValues = array_values($values);
            $firstRowOfArray = array_shift($arrayValues);

            $labels = array_keys($firstRowOfArray);
        }

        $csv = Writer::createFromFileObject(new \SplTempFileObject());

        $csv->insertOne($labels);
        $csv->insertAll($values);
        $csv->output($filename);

        exit(0);
    }
}
