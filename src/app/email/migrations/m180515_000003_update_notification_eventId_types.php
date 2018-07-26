<?php

namespace barrelstrength\sproutbase\app\email\migrations;

use craft\db\Migration;

/**
 * m180515_000003_update_notification_eventId_types migration.
 */
class m180515_000003_update_notification_eventId_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $types = [
            0 => [
                'oldType' => 'sproutforms-basicsproutformsnotification',
                'newType' => 'barrelstrength\sproutforms\integrations\sproutemail\emailtemplates\basic\BasicSproutFormsNotification'
            ],
            1 => [
                'oldType' => 'sproutemail-basictemplates',
                'newType' => 'barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates'
            ]
        ];

        foreach ($types as $type) {
            $this->update('{{%sproutemail_notificationemails}}', [
                'emailTemplateId' => $type['newType']
            ], ['emailTemplateId' => $type['oldType']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180515_000003_update_notification_eventId_types cannot be reverted.\n";
        return false;
    }
}
