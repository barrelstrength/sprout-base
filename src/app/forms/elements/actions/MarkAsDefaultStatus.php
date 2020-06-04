<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\elements\actions;

use barrelstrength\sproutbase\app\forms\models\EntryStatus;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use Throwable;

/**
 *
 * @property string $triggerLabel
 */
class MarkAsDefaultStatus extends ElementAction
{
    /**
     * @var string|null The confirmation message that should be shown before the elements get deleted
     */
    public $confirmationMessage;

    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $successMessage;

    /**
     * @var EntryStatus
     */
    public $entryStatus;

    public function init()
    {
        parent::init();

        $this->entryStatus = SproutBase::$app->entryStatuses->getDefaultEntryStatus();
    }

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('sprout', 'Mark as '.$this->entryStatus->name);
    }

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        return Craft::t('sprout', 'Are you sure you want to mark the selected form entries as {statusName}', [
            'statusName' => $this->entryStatus->name
        ]);
    }

    /**
     * @inheritdoc
     * @throws Throwable
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $message = null;

        $response = SproutBase::$app->entryStatuses->markAsDefaultStatus($query->all());

        if ($response) {
            $message = Craft::t('sprout', 'Entries marked as {statusName}.', [
                'statusName' => $this->entryStatus->name
            ]);
        } else {
            $message = Craft::t('sprout', 'Unable to mark entries as {statusName}.', [
                'statusName' => $this->entryStatus->name
            ]);
        }

        $this->setMessage($message);

        return $response;
    }
}
