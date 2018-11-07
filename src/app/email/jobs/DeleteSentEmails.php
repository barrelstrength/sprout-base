<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\email\jobs;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutemail\elements\SentEmail;
use craft\queue\BaseJob;
use Craft;

/**
 * Delete404 job
 */
class DeleteSentEmails extends BaseJob
{
    public $siteId;
    public $totalToDelete;

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
        $sentEmails = SentEmail::find()
            ->limit($this->totalToDelete)
            ->orderBy(['id' => SORT_ASC])
            ->anyStatus()
            ->siteId($this->siteId)
            ->all();

        $totalSteps = count($sentEmails);

        if (!empty($sentEmails)) {
            foreach ($sentEmails as $key => $sentEmail) {
                $step = $key + 1;
                $this->setProgress($queue, $step / $totalSteps);

                $response = Craft::$app->elements->deleteElementById($sentEmail->id);

                if (!$response) {
                    SproutBase::error('SproutSeo has failed to delete the 404 redirect Id:'.$sentEmail->id);
                }
            }
        }

        return true;
    }
}