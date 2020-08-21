<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\redirects\elements\actions;

use barrelstrength\sproutbase\app\redirects\enums\RedirectStatusCodes;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use yii\db\Exception;

class ChangeTemporaryStatusCode extends ElementAction
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
        return Craft::t('sprout', 'Update Status Code to 302');
    }

    public function getConfirmationMessage()
    {
        return $this->confirmationMessage;
    }

    /**
     * @inheritDoc
     *
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

        $response = SproutBase::$app->redirects->updateRedirectStatusCode($elementIds, RedirectStatusCodes::Temporary);

        $message = SproutBase::$app->redirects->getStatusCodeUpdateResponse($response);

        $this->setMessage($message);

        return $response;
    }
}
