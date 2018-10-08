<?php

namespace barrelstrength\sproutbase\app\import\queue\jobs;

use barrelstrength\sproutbase\SproutBase;
use craft\queue\BaseJob;
use Craft;

class Seed extends BaseJob
{
    /**
     * @var $seedJob
     */
    public $seedJob;

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function execute($queue)
    {
        $seedJob = $this->seedJob;

        SproutBase::$app->seed->runSeed($seedJob);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('sprout-import', 'Seeding Data.');
    }
}
