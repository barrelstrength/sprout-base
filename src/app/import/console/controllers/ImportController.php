<?php

namespace barrelstrength\sproutbase\app\import\console\controllers;

use yii\console\Controller;
use barrelstrength\sproutbase\app\import\models\Seed;
use barrelstrength\sproutbase\app\import\enums\ImportType;
use barrelstrength\sproutbase\app\import\models\Json as JsonModel;
use Craft;
use yii\console\ExitCode;
use barrelstrength\sproutbase\app\import\queue\jobs\Import;

class ImportController extends Controller
{
    /**
     * @var string The file(s) to import.
     */
    public $file;

    /**
     * @var bool Track the imported data as seed data
     */
    public $seed = false;

    /**
     * @inheritdoc
     */
    public $defaultAction = 'run';

    /**
     * @param string $actionID
     *
     * @return array|string[]
     */
    public function options($actionID)
    {
        return ['file', 'seed'];
    }

    /**
     * @inheritdoc
     */
    public function optionAliases()
    {
        $aliases = parent::optionAliases();
        $aliases['f'] = 'file';
        $aliases['s'] = 'seed';

        return $aliases;
    }

    /**
     * Queue one or more files for import
     */
    public function actionRun()
    {
        if (!$this->file) {
            $message = Craft::t("sprout-base", "Invalid attribute: --file requires a valid file path");
            $this->stdout($message);

            return ExitCode::DATAERR;
        }

        $paths = array_map('trim', explode(',', $this->file));

        if ($paths) {
            foreach ($paths as $path) {
                $filepath = Craft::getAlias($path);
                $this->queueFile($filepath);

                $message = Craft::t("sprout-import", $filepath." queued for import.");
                $this->stdout($message.PHP_EOL);
            }
        }
    }

    /**
     * @param $path
     *
     * @return int|null
     */
    private function queueFile($path)
    {
        if (!file_exists($path)) {
            $message = Craft::t("sprout-import", "File path does not exist.");
            $this->stdout($message);

            return ExitCode::DATAERR;
        }

        $fileContent = file_get_contents($path);

        if ($fileContent) {
            $importData = new JsonModel();
            $importData->setJson($fileContent);

            // Make sure we have JSON
            if ($importData->hasErrors()) {
                $errorMessage = $importData->getFirstError('json');

                $this->stdout($errorMessage);

                return ExitCode::DATAERR;
            }

            $seedModel = new Seed();
            $seedModel->seedType = ImportType::Console;
            $seedModel->enabled = $this->seed;

            $fileImportJob = new Import();
            $fileImportJob->seedAttributes = $seedModel->getAttributes();
            $fileImportJob->importData = $importData->json;

            Craft::$app->queue->push(new Import([
                'importData' => $fileImportJob->importData,
                'seedAttributes' => $fileImportJob->seedAttributes
            ]));
        }

        return null;
    }
}