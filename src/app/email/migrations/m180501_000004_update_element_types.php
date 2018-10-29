<?php

namespace barrelstrength\sproutbase\app\email\migrations;

use craft\db\Migration;

class m180501_000004_update_element_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Updates from Craft 2
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
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180725_080640_update_element_types cannot be reverted.\n";
        return false;
    }
}
