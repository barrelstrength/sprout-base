<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\redirects\jobs;

use barrelstrength\sproutbase\app\redirects\elements\Redirect;
use barrelstrength\sproutbase\app\redirects\records\Redirect as RedirectRecord;
use Craft;
use craft\db\Query;
use craft\db\Table;
use craft\queue\BaseJob;
use craft\queue\QueueInterface;
use Throwable;
use yii\queue\Queue;

/**
 * DeleteSoftDeletedRedirects job
 *
 * @deprecated - One time use for migration
 *
 * This job is probably only necessary for the m190806_000000_delete_soft_deleted_redirect_elements
 * for sprout-base-redirects v1.1.0 and we can probably just remove it in the next release or for the next
 * major release.
 */
class DeleteSoftDeletedRedirects extends BaseJob
{
    /**
     * @param QueueInterface|Queue $queue
     *
     * @return bool
     * @throws Throwable
     */
    public function execute($queue): bool
    {
        // Get all Soft Deleted Redirects. We are removing support for Soft Deletes.
        $redirects = (new Query())
            ->select([
                'redirects.id AS redirectId',
                'elements_sites.siteId AS siteId'
            ])
            ->from([RedirectRecord::tableName().' redirects'])
            ->innerJoin(Table::ELEMENTS.' elements', '[[redirects.id]] = [[elements.id]]')
            ->innerJoin(Table::ELEMENTS_SITES.' elements_sites', '[[elements_sites.elementId]] = [[elements.id]]')
            ->where(['not', ['elements.dateDeleted' => null]])
            ->all();

        $totalSteps = count($redirects);

        foreach ($redirects as $key => $redirect) {
            $step = $key + 1;
            $this->setProgress($queue, $step / $totalSteps);

            $element = Craft::$app->elements->getElementById($redirect['redirectId'], Redirect::class, $redirect['siteId'], [
                'trashed' => true
            ]);

            if ($element && !Craft::$app->elements->deleteElement($element, true)) {
                Craft::error('Unable to delete the Soft Deleted Redirect Element ID:'.$redirect['redirectId'], __METHOD__);
            } else {
                Craft::error('Deleted the Soft Deleted Redirect Element ID:'.$redirect['redirectId'], __METHOD__);
            }
        }

        return true;
    }

    /**
     * Returns the default description for this job.
     *
     * @return string
     */
    protected function defaultDescription(): string
    {
        return Craft::t('sprout', 'Deleting soft deleted Redirect Elements');
    }
}