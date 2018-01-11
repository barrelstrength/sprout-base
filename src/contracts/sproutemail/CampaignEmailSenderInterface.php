<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\contracts\sproutemail;

use barrelstrength\sproutemail\elements\CampaignEmail;
use barrelstrength\sproutemail\models\CampaignType;

interface CampaignEmailSenderInterface
{
    /**
     * @param CampaignEmail $campaignEmail
     * @param CampaignType  $campaign
     *
     * @return mixed
     * @internal param SproutEmail_CampaignEmailModel $campaignEmail
     */
    public function sendCampaignEmail(CampaignEmail $campaignEmail, CampaignType $campaignType);
}
