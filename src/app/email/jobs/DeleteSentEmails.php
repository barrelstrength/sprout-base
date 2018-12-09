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
    /**
     * @var int
     */
    public $siteId;

    /**
     * @var int
     */
    public $limit;

    /**
     * Returns the default description for this job.
     *
     * @return string
     */
    protected function defaultDescription(): string
    {
        return Craft::t('sprout-base', 'Cleaning up Sent Email');
    }

    /**
     * @param \craft\queue\QueueInterface|\yii\queue\Queue $queue
     *
     * @return bool
     * @throws \Throwable
     */
    public function execute($queue): bool
    {
        /** @var SentEmail[] $sentEmails */
        $sentEmails = SentEmail::find()
            ->limit(null)
            ->offset($this->limit)
            ->orderBy(['sproutemail_sentemail.dateCreated' => SORT_DESC])
            ->anyStatus()
            ->siteId($this->siteId)
            ->all();

        $totalSteps = count($sentEmails);

        if (empty($sentEmails)) {
            return true;
        }

        foreach ($sentEmails as $key => $sentEmail) {
            $step = $key + 1;
            $this->setProgress($queue, $step / $totalSteps);

            $response = Craft::$app->elements->deleteElementById($sentEmail->id);

            if (!$response) {
                SproutBase::error('Unable to delete Sent Email ID: '.$sentEmail->id);
            }
        }

        return true;
    }
}