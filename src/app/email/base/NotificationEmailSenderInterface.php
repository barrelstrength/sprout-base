<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\email\base;

interface NotificationEmailSenderInterface
{
    /**
     * Gives a mailer the responsibility to send Notification Emails
     * if they implement NotificationEmailSenderInterface
     *
     * @param EmailElement $notificationEmail
     *
     * @return bool
     */
    public function sendNotificationEmail(EmailElement $notificationEmail);
}
