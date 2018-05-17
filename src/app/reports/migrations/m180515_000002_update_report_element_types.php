<?php

namespace barrelstrength\sproutbase\app\reports\migrations;

use craft\db\Migration;

/**
 * m180515_000002_update_report_element_types migration.
 */
class m180515_000002_update_report_element_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $seedClasses = [
            0 => [
                'oldType' => 'barrelstrength\sproutbase\elements\sproutreports\Report',
                'newType' => 'barrelstrength\sproutbase\app\reports\elements\Report'
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
        echo "m180515_000002_update_report_element_types cannot be reverted.\n";
        return false;
    }
}
