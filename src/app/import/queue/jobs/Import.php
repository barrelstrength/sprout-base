<?php

namespace barrelstrength\sproutbase\app\import\queue\jobs;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\app\import\models\Seed;
use barrelstrength\sproutbase\app\import\models\Weed;
use craft\helpers\Json;
use craft\queue\BaseJob;
use Craft;
use yii\base\Exception;
use yii\helpers\VarDumper;

class Import extends BaseJob
{
    public $importData;

    public $seedAttributes;

    /**
     * @inheritdoc
     *
     * @param $queue
     *
     * @throws \Throwable
     */
    public function execute($queue)
    {
        $seedModel = new Seed();
        $seedModel->setAttributes($this->seedAttributes, false);

        try {

            $weedModelAttributes = [
                'seed' => $seedModel->enabled,
                'seedType' => $seedModel->seedType,
                'details' => Craft::t('sprout-base', 'Import Type: '.$seedModel->seedType),
                'dateSubmitted' => $seedModel->dateCreated
            ];

            $weedModel = new Weed();
            $weedModel->setAttributes($weedModelAttributes, false);

            $this->importData = Json::decode($this->importData, true);

            SproutBase::$app->importers->save($this->importData, $weedModel);

            $errors = SproutBase::$app->importUtilities->getErrors();

            if (!empty($errors)) {

                $errors = VarDumper::dumpAsString($errors);

                $message = Craft::t('sprout-base', 'Error(s) while running Sprout Import job.');

                SproutBase::error($message);
                SproutBase::error($errors);

                throw new Exception($message);
            }
        } catch (\Exception $e) {
            SproutBase::error('Unable to run Sprout Import job.');
            SproutBase::error($e->getMessage());

            throw $e;
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('sprout-base', 'Importing Data.');
    }
}
