<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\email\events\notificationevents;

use barrelstrength\sproutbase\app\email\base\NotificationEvent;
use Craft;

class Manual extends NotificationEvent
{
    public function getEventClassName()
    {
        return null;
    }

    public function getEventName()
    {
        return null;
    }

    public function getEventHandlerClassName()
    {
        return null;
    }

    public function getName(): string
    {
        return Craft::t('sprout', 'None');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'The manual event is never triggered.');
    }
}
