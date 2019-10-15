<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\jobs;

use barrelstrength\sproutbase\SproutBase;
use craft\queue\BaseJob;
use Craft;

use craft\queue\QueueInterface;
use Throwable;
use yii\queue\Queue;

/**
 * PurgeElements job
 */
class PurgeElements extends BaseJob
{
    public $siteId;
    public $idsToDelete;
    public $idsToExclude;
    public $elementType;

    /**
     * Returns the default description for this job.
     *
     * @return string
     */
    protected function defaultDescription(): string
    {
        return Craft::t('sprout-base', 'Deleting oldest '.$this->elementType);
    }

    /**
     * @param QueueInterface|Queue $queue
     *
     * @return bool
     * @throws Throwable
     */
    public function execute($queue): bool
    {
        $totalSteps = count($this->idsToDelete);

        foreach ($this->idsToDelete as $key => $id) {
            $step = $key + 1;
            $this->setProgress($queue, $step / $totalSteps);

            $element = Craft::$app->elements->getElementById($id, $this->elementType, $this->siteId);

            if ($element && !Craft::$app->elements->deleteElement($element, true)) {
                SproutBase::error('Unable to delete the '.$this->elementType.' element type using ID:'.$id);
            }
        }

        return true;
    }
}