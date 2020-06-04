<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\redirects\elements\actions;

use barrelstrength\sproutbase\app\redirects\enums\RedirectMethods;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use yii\db\Exception;

/**
 *
 * @property string $triggerLabel
 */
class ChangeTemporaryMethod extends ElementAction
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
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('sprout', 'Update Method to 302');
    }

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        return $this->confirmationMessage;
    }

    /**
     * @param ElementQueryInterface $query
     *
     * @return bool
     * @throws Exception
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $elementIds = $query->ids();
        $total = count($elementIds);

        if (!SproutBase::$app->redirects->canCreateRedirects($total)) {
            $this->setMessage(Craft::t('sprout', 'Upgrade to PRO to manage additional redirect rules'));

            return false;
        }

        // Call updateMethods service
        $response = SproutBase::$app->redirects->updateRedirectMethod($elementIds, RedirectMethods::Temporary);

        $message = SproutBase::$app->redirects->getMethodUpdateResponse($response);

        $this->setMessage($message);

        return $response;
    }
}
