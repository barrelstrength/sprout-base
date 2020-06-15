<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\migrations\redirects;

use barrelstrength\sproutbase\app\redirects\elements\Redirect;
use barrelstrength\sproutbase\app\redirects\records\Redirects as RedirectRecord;
use craft\db\Migration;
use craft\db\Table;

class Install extends Migration
{
    /**
     * @return bool|void
     */
    public function safeUp()
    {
        if (!$this->db->tableExists(RedirectRecord::tableName())) {
            $this->createTable(RedirectRecord::tableName(), [
                'id' => $this->primaryKey(),
                'oldUrl' => $this->string()->notNull(),
                'newUrl' => $this->string(),
                'method' => $this->integer(),
                'matchStrategy' => $this->string()->defaultValue('exactMatch'),
                'count' => $this->integer()->defaultValue(0),
                'lastRemoteIpAddress' => $this->string(),
                'lastReferrer' => $this->string(),
                'lastUserAgent' => $this->string(),
                'dateLastUsed' => $this->dateTime(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            $this->createIndex(null, RedirectRecord::tableName(), 'id');

            $this->addForeignKey(null, RedirectRecord::tableName(), 'id', Table::ELEMENTS, 'id', 'CASCADE');
        }
    }

    /**
     * @return bool|void
     */
    public function safeDown()
    {
        // Delete Redirect Elements
        $this->delete(Table::ELEMENTS, ['type' => Redirect::class]);

        // Delete Redirect Table
        $this->dropTableIfExists(RedirectRecord::tableName());
    }
}
