<?php

namespace barrelstrength\sproutbase\app\reports\elements\actions;

use Craft;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use Throwable;

class DeleteReport extends Delete
{
    /**
     * @var string|null The confirmation message that should be shown before the elements get deleted
     */
    public $confirmationMessage = 'Are you sure you want to delete the selected reports?';

    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $successMessage = 'Reports deleted.';

    /**
     *  Performs the action on any elements that match the given criteria.
     *  return Whether the action was performed successfully.
     *
     * @param ElementQueryInterface $query
     *
     * @return bool
     * @throws Throwable
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $elementsService = Craft::$app->getElements();
        foreach ($query->all() as $element) {
            $elementsService->deleteElement($element, true);
        }

        $this->setMessage(Craft::t('sprout', 'Reports deleted.'));

        return true;
    }
}
