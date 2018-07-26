<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\email\base;

use barrelstrength\sproutemail\elements\CampaignEmail;
use barrelstrength\sproutemail\models\CampaignType;

interface CampaignEmailSenderInterface
{
    /**
     * Gives a mailer the responsibility to send Campaign Emails
     * if they implement CampaignEmailSenderInterface
     *
     * @param CampaignEmail $campaignEmail
     * @param CampaignType  $campaignType
     *
     * @return mixed
     */
    public function sendCampaignEmail(CampaignEmail $campaignEmail, CampaignType $campaignType);

    /**
     * @todo - change method signature and remove $emails in favor of $campaignEmail->getRecipients()
     *
     * @param CampaignEmail $campaignEmail
     * @param CampaignType  $campaignType
     * @param array         $emails
     *
     * @return null
     */
    public function sendTestCampaignEmail(CampaignEmail $campaignEmail, CampaignType $campaignType, array $emails = []);
}
