<?php

namespace barrelstrength\sproutbase\app\import\console\controllers;

use barrelstrength\sproutbase\SproutBase;
use craft\helpers\DateTimeHelper;
use yii\console\Controller;
use craft\helpers\Json;
use Craft;
use yii\console\ExitCode;
use barrelstrength\sproutbase\app\import\models\jobs\SeedJob;
use barrelstrength\sproutbase\app\import\enums\ImportType;

class SeedController extends Controller
{
    /**
     * @var
     */
    public $content;

    /**
     * @var
     */
    public $settingPath;

    /**
     * @var integer The number of items you would like to seed to your database
     */
    public $quantity;

    /**
     * @inheritdoc
     */
    public $defaultAction = 'generate';

    /**
     * @param string $actionID
     *
     * @return array|string[]
     */
    public function options($actionID)
    {
        return ['content', 'quantity', 'settingPath'];
    }

    /**
     * Seed your database with dummy content
     */
    public function actionGenerate()
    {
        if (!file_exists($this->settingPath)) {
            $message = Craft::t("sprout-import", "File path does not exist.");
            $this->stdout($message);

            return ExitCode::DATAERR;
        }

        $jsonSetting = file_get_contents($this->settingPath);

        $setting = Json::decode($this->settingPath);

        $weedMessage = Craft::t('sprout-import', '{elementType} Element(s)');

        $details = Craft::t('sprout-import', $weedMessage, [
            'elementType' => $this->content
        ]);

        $seedJob = new SeedJob();
        $seedJob->elementType = $this->content;
        $seedJob->quantity = !empty($this->quantity) ? $this->quantity : 11;
        $seedJob->settings = $setting;
        $seedJob->seedType = ImportType::Seed;
        $seedJob->details = $details;
        $seedJob->dateCreated = DateTimeHelper::currentUTCDateTime();

        $seedJobErrors = null;

        if (SproutBase::$app->seed->generateSeeds($seedJob)) {
            $message = Craft::t("sprout-import", $this->content." seed in queue.");
            $this->stdout($message.PHP_EOL);
        }

        return null;
    }
}