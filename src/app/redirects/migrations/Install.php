<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\redirects\migrations;

use barrelstrength\sproutbase\app\redirects\elements\Redirect;
use barrelstrength\sproutbase\app\redirects\records\Redirect as RedirectRecord;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use craft\errors\StructureNotFoundException;
use craft\models\Structure;
use Throwable;
use yii\base\Exception;

class Install extends Migration
{
    /**
     * @return bool
     * @throws Throwable
     */
    public function safeUp(): bool
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
     * @return bool
     */
    public function safeDown(): bool
    {
        // Delete Redirect Elements
        $this->delete(Table::ELEMENTS, ['type' => Redirect::class]);

        // Delete Redirect Table
        $this->dropTableIfExists(RedirectRecord::tableName());
    }
}
