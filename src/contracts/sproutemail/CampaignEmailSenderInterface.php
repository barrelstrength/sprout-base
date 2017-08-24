<?php

namespace barrelstrength\sproutcore\contracts\sproutemail;

use barrelstrength\sproutemail\elements\CampaignEmail;
use barrelstrength\sproutemail\models\CampaignTypeModel;

interface CampaignEmailSenderInterface
{
	/**
	 * @param CampaignEmail $campaignEmail
	 * @param CampaignTypeModel  $campaign
	 *
	 * @return mixed
	 * @internal param SproutEmail_CampaignEmailModel $campaignEmail
	 */
	public function sendCampaignEmail(CampaignEmail $campaignEmail, CampaignTypeModel $campaignType);
}
