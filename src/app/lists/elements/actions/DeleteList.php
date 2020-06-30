<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\elements\actions;

use barrelstrength\sproutbase\app\lists\elements\ListElement;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use Exception;
use Throwable;

/**
 * Class DeleteList
 *
 * @package barrelstrength\sproutbase\app\lists\elements\actions
 */
class DeleteList extends Delete
{
    /**
     * @var string|null The confirmation message that should be shown before the elements get deleted
     */
    public $confirmationMessage = 'Are you sure you want to delete this list(s)?';

    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $successMessage = 'List(s) deleted.';

    /**
     * @param ElementQueryInterface $query
     *
     * @return bool
     * @throws Exception
     * @throws Throwable
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        /**
         * @var ListElement[] $lists
         */
        $lists = $query->all();

        // Delete the users
        foreach ($lists as $list) {
            $listType = SproutBase::$app->lists->getListType($list->type);
            $listType->deleteList($list);
        }

        $this->setMessage(Craft::t('sprout', 'List(s) deleted.'));

        return true;
    }
}
