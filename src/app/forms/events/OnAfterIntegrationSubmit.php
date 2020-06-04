<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\events;

use barrelstrength\sproutbase\app\forms\models\IntegrationLog;
use yii\base\Event;

/**
 * OnAfterIntegrationSubmit class.
 */
class OnAfterIntegrationSubmit extends Event
{
    /**
     * @var IntegrationLog
     */
    public $integrationLog;
}
