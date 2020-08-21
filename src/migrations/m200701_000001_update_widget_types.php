<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbase\migrations;

use craft\db\Migration;

class m200701_000001_update_widget_types extends Migration
{
    public function safeUp()
    {
        $types = [
            [
                'oldType' => 'barrelstrength\sproutbasereports\widgets\Number',
                'newType' => 'barrelstrength\sproutbase\app\reports\widgets\Number'
            ],
            [
                'oldType' => 'barrelstrength\sproutbasereports\widgets\Visualization',
                'newType' => 'barrelstrength\sproutbase\app\reports\widgets\Visualization'
            ],
        ];

        foreach ($types as $type) {
            $this->update('{{%widgets}}', [
                'type' => $type['newType']
            ], ['type' => $type['oldType']], [], false);
        }
    }

    public function safeDown(): bool
    {
        echo "m200701_000001_update_widget_types cannot be reverted.\n";

        return false;
    }
}
