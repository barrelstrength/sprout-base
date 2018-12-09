<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbase\app\email\migrations;

use craft\db\Migration;

class m180501_000004_update_element_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
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

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180725_080640_update_element_types cannot be reverted.\n";
        return false;
    }
}
