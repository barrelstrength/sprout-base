<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\elements\actions;

use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use Throwable;

class MarkAsSpam extends ElementAction
{
    /**
     * @var string|null The confirmation message that should be shown before the elements get deleted
     */
    public $confirmationMessage;

    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $successMessage;

    public function getTriggerLabel(): string
    {
        return Craft::t('sprout', 'Mark as Spam');
    }

    public function getConfirmationMessage()
    {
        return Craft::t('sprout', 'Are you sure you want to mark the selected form entries as Spam?');
    }

    /**
     * @inheritDoc
     *
     * @throws Throwable
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $message = null;

        $response = SproutBase::$app->formEntryStatuses->markAsSpam($query->all());

        if ($response) {
            $message = Craft::t('sprout', 'Entries marked as Spam.');
        } else {
            $message = Craft::t('sprout', 'Unable to mark entries as Spam');
        }

        $this->setMessage($message);

        return $response;
    }
}
