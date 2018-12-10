<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\email\base;

use barrelstrength\sproutemail\elements\CampaignEmail;

interface CampaignEmailSenderInterface
{
    /**
     * Gives a mailer the responsibility to send Campaign Emails
     * if they implement CampaignEmailSenderInterface
     *
     * @param CampaignEmail $campaignEmail
     *
     * @return mixed
     */
    public function sendCampaignEmail(CampaignEmail $campaignEmail);
}
