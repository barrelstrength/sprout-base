<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services;

use barrelstrength\sproutbase\jobs\PurgeElements;
use Craft;
use craft\base\Element;
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
