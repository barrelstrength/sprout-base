<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\redirects\elements\actions;

use barrelstrength\sproutbase\app\redirects\elements\Redirect;
use barrelstrength\sproutbase\config\services\Config;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use Exception;
use Throwable;
use yii\base\InvalidConfigException;
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

            if (!SproutBase::$app->settings->saveSettings(Config::CONFIG_SPROUT_KEY.'.redirects', $redirectSettings)) {
                throw new InvalidConfigException('Unable to save Excluded URL Patterns');
            }

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
        }

        $this->setMessage(Craft::t('sprout', 'Added to Excluded URL Patterns setting.'));

        return true;
    }
}
