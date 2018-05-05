<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\sproutemail\contracts;

use barrelstrength\sproutbase\sproutemail\elements\NotificationEmail;

interface NotificationEmailSenderInterface
{
    /**
     * Gives a mailer the responsibility to send Notification Emails
     * if they implement NotificationEmailSenderInterface
     *
     * @param NotificationEmail $notificationEmail
     * @param                   $object
     *
     * @return bool
     */
    public function sendNotificationEmail(NotificationEmail $notificationEmail, $object = null);
}
