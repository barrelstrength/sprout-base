<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbase\app\email\migrations;

use craft\db\Migration;

/**
 * m180515_000001_update_notification_element_types migration.
 */
class m180501_000001_update_notification_element_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Craft 2 Updates
        $seedClasses = [
            0 => [
                'oldType' => 'barrelstrength\sproutbase\elements\sproutemail\NotificationEmail',
                'newType' => 'barrelstrength\sproutbase\app\email\elements\NotificationEmail'
            ]
        ];

        foreach ($seedClasses as $seedClass) {
            $this->update('{{%elements}}', [
                'type' => $seedClass['newType']
            ], ['type' => $seedClass['oldType']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180515_000001_update_notification_element_types cannot be reverted.\n";
        return false;
    }
}
