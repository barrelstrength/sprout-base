<?php

namespace barrelstrength\sproutbase\app\redirects\elements\actions;

use Craft;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use Throwable;

/**
 * HardDelete overrides a Delete element action to Hard Delete Redirects
 */
class HardDelete extends Delete
{
    /**
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

        $this->setMessage($this->successMessage);

        return true;
    }
}
