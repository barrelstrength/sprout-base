<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\reports\services;

use Craft;
use craft\helpers\Json;
use Exception;
use League\Csv\Writer;
use SplTempFileObject;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Component;

class Exports extends Component
{
    /**
     * @param array $values
     * @param array $labels
     * @param array $variables
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \yii\base\Exception
     */
    public function toHtml(array &$values, array $labels = [], array $variables = []): string
    {
        // @todo - reconsider this logic
        if (empty($labels) && !empty($values)) {
            $arrayValues = array_values($values);
            $firstRowOfArray = array_shift($arrayValues);

            $labels = array_keys($firstRowOfArray);
        }

        $variables['labels'] = $labels;
        $variables['values'] = $values;

        return Craft::$app->getView()->renderTemplate('sprout-base-config/results/index', $variables);
    }

    /**
     * @param array $values
     *
     * @return string
     * @throws Exception
     */
    public function toJson(array &$values): string
    {
        return Json::encode($values);
    }

    /**
     * Takes an array of values and options labels and creates a downloadable CSV file
     *
     * @param array  $values
     * @param array  $labels
     * @param string $filename
     * @param null   $delimiter
     */
    public function toCsv(array &$values, array $labels = [], $filename = 'export.csv', $delimiter = null)
    {
        $filename = str_replace('.csv', '', $filename).'.csv';

        if (empty($labels) && !empty($values)) {
            $arrayValues = array_values($values);
            $firstRowOfArray = array_shift($arrayValues);

            $labels = array_keys($firstRowOfArray);
        }

        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // Defaults to comma-delimited
        if ($delimiter) {
            $csv->setDelimiter($delimiter);
        }

        $csv->insertOne($labels);
        $csv->insertAll($values);
        $csv->output($filename);

        exit(0);
    }
}
