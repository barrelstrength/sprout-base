<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\services;

use barrelstrength\sproutbase\helpers\jobs\PurgeElements;
use Craft;
use yii\base\Component;

class Utilities extends Component
{
    public function purgeElements(PurgeElements $purgeElementsJob, $delay = null)
    {
        if ($delay) {
            Craft::$app->getQueue()->delay($delay)->push($purgeElementsJob);
        } else {
            Craft::$app->getQueue()->push($purgeElementsJob);
        }
    }
}
