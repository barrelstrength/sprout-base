<?php

namespace barrelstrength\sproutbase\app\email\enums;

/**
 * The DeliveryStatus class defines the available delivery statuses for Sent Emails
 */
abstract class DeliveryStatus
{
    /**
     * The message was handed off to the email service provider
     */
    const Sent = 'Sent';

    /**
     * The sent email encountered an error and Craft was unable to hand off the email to the email service provider
     */
    const Error = 'Error';
}
