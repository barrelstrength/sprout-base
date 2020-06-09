<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\twig\variables;

use Craft;
use yii\base\InvalidConfigException;

class SproutCampaignsVariable
{
    /**
     * Returns the value of the displayDateScheduled general config setting
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function getDisplayDateScheduled(): bool
    {
        $config = Craft::$app->getConfig()->getConfigSettings('general');

        if (!is_array($config)) {
            return false;
        }

        return $config->displayDateScheduled ?? false;
    }
}