<?php

namespace barrelstrength\sproutbase\app\reports\elements\actions;

use Craft;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;

/**
 * DeleteReport represents a Delete reports element action.
 *
 * @package barrelstrength\sproutbase\app\email\elements\actions
 */
class DeleteReport extends Delete
{
    /**
     * @var string|null The confirmation message that should be shown before the elements get deleted
     */
    public $confirmationMessage = 'Are you sure you want to delete this report(s)?';

    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $successMessage = 'report(s) deleted.';

    /**
     *  Performs the action on any elements that match the given criteria.
     *  return Whether the action was performed successfully.
     *
     * @param ElementQueryInterface $query
     *
     * @return bool
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        parent::performAction($query);

        $this->setMessage(Craft::t('app', 'report(s) deleted.'));

        return true;
    }
}
