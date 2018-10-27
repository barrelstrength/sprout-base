<?php

namespace barrelstrength\sproutbase\app\email\migrations;

use craft\db\Migration;
use craft\db\Query;

/**
 * m181026_000000_update_notification_data migration.
 */
class m181026_000000_update_notification_data extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Craft 2 envent ids
        $types = [
            0 => [
                'oldType' => 'SproutEmail-users-saveUser',
                'newType' => 'barrelstrength\sproutemail\events\notificationevents\UsersSave',
                'pluginHandle' => 'sprout-email'
            ],

            1 => [
                'oldType' => 'SproutForms-sproutForms-saveEntry',
                'newType' => 'barrelstrength\sproutforms\integrations\sproutemail\events\notificationevents\SaveEntryEvent',
                'pluginHandle' => 'sprout-forms'
            ],

            2 => [
                'oldType' => 'SproutEmail-users-deleteUser',
                'newType' => 'barrelstrength\sproutemail\events\notificationevents\UsersDelete',
                'pluginHandle' => 'sprout-email'
            ],

            3 => [
                'oldType' => 'SproutEmail-entries-saveEntry',
                'newType' => 'barrelstrength\sproutemail\events\notificationevents\EntriesSave',
                'pluginHandle' => 'sprout-email'
            ],

            4 => [
                'oldType' => 'SproutEmail-entries-deleteEntry',
                'newType' => 'barrelstrength\sproutemail\events\notificationevents\EntriesDelete',
                'pluginHandle' => 'sprout-email'
            ]
        ];

        foreach ($types as $type) {
            $notifications = (new Query())
                ->select(['id', 'settings'])
                ->from(['{{%sproutemail_notificationemails}}'])
                ->where(["eventId" => $type['oldType']])
                ->all();

            if ($notifications){
                foreach ($notifications as $notification){
                    $options = json_decode($notification['settings'], true);
                    $newOptions = [];
                    if (isset($options['craft'])){
                        if (isset($options['craft']['saveUser'])) {
                            $newOptions = $options['craft']['saveUser'];
                        }
                        if (isset($options['craft']['deleteUser'])) {
                            $newOptions = $options['craft']['deleteUser'];
                        }
                        if (isset($options['craft']['saveEntry'])) {
                            $newOptions = $options['craft']['saveEntry'];
                        }
                        if (isset($options['craft']['deleteEntry'])) {
                            $newOptions = $options['craft']['deleteEntry'];
                        }
                    }else if(isset($options['sproutForms'])){
                        if (isset($options['sproutForms']['saveEntry'])) {
                            $newOptions = $options['sproutForms']['saveEntry'];
                        }
                    }

                    $this->update('{{%sproutemail_notificationemails}}', [
                        'eventId' => $type['newType'],
                        'pluginHandle' => $type['pluginHandle'],
                        'settings' => json_encode($newOptions)
                    ], ['id' => $notification['id']], [], false);
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181026_000000_update_notification_data cannot be reverted.\n";
        return false;
    }
}
