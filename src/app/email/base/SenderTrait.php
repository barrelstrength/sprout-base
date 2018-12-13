<?php

namespace barrelstrength\sproutbase\app\email\base;

use barrelstrength\sproutemail\elements\CampaignEmail;
use Craft;

trait SenderTrait
{
    public $defaultFromName;
    public $defaultFromEmail;
    public $defaultReplyToEmail;

    /**
     * Returns if a Mailer supports a Sender
     *
     * This setting is mostly to support the Copy/Paste Mailer use case where a user is using
     * Sprout Email to prepare an email to be sent from another platform
     *
     * @return bool
     */
    public function hasSender(): bool
    {
        return true;
    }

    /**
     * @param CampaignEmail $campaignEmail
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSenderHtml(CampaignEmail $campaignEmail): string
    {
        // @todo - override default from,email, and replyTo with values from default settings

        return Craft::$app->getView()->renderTemplate('sprout-base-email/_components/mailers/recipients-html', [
            'campaignEmail' => $campaignEmail
        ]);
    }
}
