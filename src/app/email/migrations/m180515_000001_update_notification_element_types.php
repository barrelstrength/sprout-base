<?php

namespace barrelstrength\sproutbase\app\email\migrations;

use craft\db\Migration;

/**
 * m180515_000001_update_notification_element_types migration.
 */
class m180515_000001_update_notification_element_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $seedClasses = [
            0 => [
                'oldType' => 'barrelstrength\sproutbase\sproutemail\elements\NotificationEmail',
                'newType' => 'barrelstrength\sproutbase\app\email\elements\NotificationEmail'
            ]
        ];

        foreach ($seedClasses as $seedClass) {
            $this->update('{{%elements}}', [
                'type' => $seedClass['newType']], ['type' => $seedClass['oldType']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180515_000001_update_notification_element_types cannot be reverted.\n";
        return false;
    }
}
