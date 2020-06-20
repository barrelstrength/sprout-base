<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\redirects\elements\actions;

use barrelstrength\sproutbase\app\redirects\elements\Redirect;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use Exception;
use Throwable;
use yii\db\Transaction;

class ExcludeUrl extends ElementAction
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
        return Craft::t('sprout', 'Add to Excluded URLs');
    }

    public function getConfirmationMessage()
    {
        return $this->confirmationMessage;
    }

    /**
     * @param ElementQueryInterface $query
     *
     * @return bool
     * @throws Exception
     * @throws Throwable
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $redirectSettings = SproutBase::$app->settings->getSettingsByKey('redirects');

        /** @var Redirect[] $redirects */
        $redirects = $query->all();

        /** @var Transaction $transaction */
        $transaction = Craft::$app->db->beginTransaction();

        try {
            foreach ($redirects as $redirect) {
                $oldUrl = $redirect->oldUrl;

                // Append the selected Old URL to the Excluded URL Pattern settings array
                $redirectSettings->excludedUrlPatterns .= PHP_EOL.$oldUrl;

                Craft::$app->elements->deleteElement($redirect, true);
            }

            // @todo - migration, fix saving of settings
            SproutBase::$app->config->saveGlobalSettings($redirectSettings->getAttributes());

            $transaction->commit();

            Craft::info('Form Saved.', __METHOD__);
        } catch (Throwable $e) {
            Craft::error('Unable to save form: '.$e->getMessage(), __METHOD__);
            $transaction->rollBack();
        }


        $this->setMessage(Craft::t('sprout', 'Added to Excluded URL Patterns setting.'));

        return true;
    }
}
