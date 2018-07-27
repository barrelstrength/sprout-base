<?php

namespace barrelstrength\sproutbase\app\email\migrations;

use Craft;
use craft\db\Migration;

/**
 * m180725_080639_add_notification_columns migration.
 */
class m180725_080640_update_element_type extends Migration
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
