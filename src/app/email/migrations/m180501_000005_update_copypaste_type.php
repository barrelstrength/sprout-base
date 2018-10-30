<?php

namespace barrelstrength\sproutbase\app\email\migrations;

use craft\db\Migration;

class m180501_000005_update_copypaste_type extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->db->tableExists('{{%sproutemail_campaigntype}}')) {
            $this->update('{{%sproutemail_campaigntype}}', [
                'mailer' => "barrelstrength\sproutemail\mailers\CopyPasteMailer"
            ], ['mailer' => 'copypaste'], [], false);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180726_080640_update_copypaste_type cannot be reverted.\n";
        return false;
    }
}
