<?php

namespace barrelstrength\sproutbase\app\email\enums;

/**
 * The DeliveryType class defines all Delivery Types for Sent Emails
 */
abstract class DeliveryType
{
    /**
     * The message is considered a live message to users or admins
     */
    const Live = 'Live';

    /**
     * The message is considered a test message
     */
    const Test = 'Test';
}
