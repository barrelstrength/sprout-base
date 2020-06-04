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
    /**
     * @inheritdoc
     */
    public function getEventClassName()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getEventName()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getEventHandlerClassName()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return Craft::t('sprout', 'None');
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return Craft::t('sprout', 'The manual event is never triggered.');
    }
}
