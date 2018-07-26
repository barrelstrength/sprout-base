<?php

namespace barrelstrength\sproutbase\app\email\migrations;

use craft\db\Migration;

class m180725_080640_update_element_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $campaignClasses = [
            0 => [
                'oldType' => 'SproutEmail_CampaignEmail',
                'newType' => 'barrelstrength\sproutbase\app\email\elements\CampaignEmail'
            ]
        ];

        foreach ($campaignClasses as $campaignClass) {
            $this->update('{{%elements}}', [
                'type' => $campaignClass['newType']
            ], ['type' => $campaignClass['oldType']], [], false);
        }

        $notificationClasses = [
            0 => [
                'oldType' => 'SproutEmail_NotificationEmail',
                'newType' => 'barrelstrength\sproutbase\app\email\elements\NotificationEmail'
            ]
        ];

        foreach ($notificationClasses as $notificationClass) {
            $this->update('{{%elements}}', [
                'type' => $notificationClass['newType']
            ], ['type' => $notificationClass['oldType']], [], false);
        }

        $sentClasses = [
            0 => [
                'oldType' => 'SproutEmail_SentEmail',
                'newType' => 'barrelstrength\sproutbase\app\email\elements\SentEmail'
            ]
        ];

        foreach ($sentClasses as $sentClass) {
            $this->update('{{%elements}}', [
                'type' => $sentClass['newType']
            ], ['type' => $sentClass['oldType']], [], false);
        }

        $this->update('{{%sproutemail_campaigntype}}', [
            'mailer' => "barrelstrength\sproutemail\mailers\CopyPasteMailer"
        ], ['mailer' => 'copypaste'], [], false);

        $this->update('{{%sproutemail_campaigntype}}', [
            'mailer' => "barrelstrength\sproutmailchimp\integrations\sproutemail\MailChimpMailer"
        ], ['mailer' => 'mailchimp'], [], false);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180725_080640_update_element_type cannot be reverted.\n";
        return false;
    }
}
