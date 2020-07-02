<?php /** @noinspection PhpDeprecationInspection */

namespace barrelstrength\sproutbase\migrations;

use craft\db\Migration;

class m200701_000000_test extends Migration
{
    public function safeUp(): bool
    {
        // ...
    }

    public function safeDown(): bool
    {
        echo "m200701_000000_test cannot be reverted.\n";

        return false;
    }
}
