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
    public function purgeElements($elementType, $idsToDelete, $delay = null, $siteId = null, $idsToExclude = null)
    {
        $purgeElements = new PurgeElements();
        $purgeElements->idsToDelete = $idsToDelete;
        $purgeElements->idsToExclude = $idsToExclude;
        $purgeElements->siteId = $siteId;
        $purgeElements->elementType = $elementType;

        if ($delay){
            Craft::$app->getQueue()->delay($delay)->push($purgeElements);
        }else{
            Craft::$app->getQueue()->push($purgeElements);
        }
    }
}
