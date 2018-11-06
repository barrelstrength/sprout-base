<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\email\jobs;

use craft\queue\BaseJob;
use Craft;

/**
 * Delete404 job
 */
class DeleteSentEmails extends BaseJob
{
    public $siteId;
    public $totalToDelete;
    public $redirectIdToExclude;

    /**
     * Returns the default description for this job.
     *
     * @return string
     */
    protected function defaultDescription(): string
    {
        return Craft::t('sprout-base', 'Deleting oldest Sent Emails');
    }

    /**
     * @param \craft\queue\QueueInterface|\yii\queue\Queue $queue
     *
     * @return bool
     * @throws \Throwable
     */
    public function execute($queue)
    {
        return true;
    }
}