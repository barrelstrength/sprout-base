<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
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

class Number extends Widget
{
    /**
     * @var string
     */
    public $heading;

    /**
     * @var string
     */
    public $description;

    /**
     * @var int
     */
    public $number;

    /**
     * @var string
     */
    public $resultPrefix;

    /**
     * @var int
     */
    public $reportId;

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Number');
    }

    public static function icon()
    {
        return Craft::getAlias('@sproutbase/app/forms/icon-mask.svg');
    }

    public function getTitle(): string
    {
        return $this->heading;
    }

    /**
     * @inheritDoc
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws Exception
     */
    public function getSettingsHtml(): string
    {
        $reportOptions = SproutBase::$app->reports->getReportsAsSelectFieldOptions();

        return Craft::$app->getView()->renderTemplate('sprout/reports/_components/widgets/Number/settings', [
                'widget' => $this,
                'reportOptions' => $reportOptions,
            ]
        );
    }

    /**
     * @inheritDoc
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    public function getBodyHtml(): string
    {
        /** @var Report $report */
        $report = Craft::$app->elements->getElementById($this->reportId, Report::class);

        if ($report) {
            $dataSource = SproutBase::$app->dataSources->getDataSourceById($report->dataSourceId);

            if ($dataSource) {
                $result = $dataSource->getResults($report);

                return Craft::$app->getView()->renderTemplate('sprout/reports/_components/widgets/Number/body',
                    [
                        'widget' => $this,
                        'result' => $this->getScalarValue($result),
                    ]
                );
            }
        }

        return Craft::$app->getView()->renderTemplate('sprout/reports/_components/widgets/Number/body',
            [
                'widget' => $this,
                'result' => Craft::t('sprout', 'NaN'),
            ]);
    }

    /**
     * @param $result
     *
     * @return int|mixed|null
     */
    protected function getScalarValue($result)
    {
        $value = null;

        if (is_array($result)) {

            if (count($result) == 1 && isset($result[0]) && count($result[0]) == 1) {
                $value = array_shift($result[0]);
            } else {
                $value = count($result);
            }
        } else {
            $value = $result;
        }

        return $value;
    }
}
