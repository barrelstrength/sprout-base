<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\sproutimport\migrations;

use craft\db\Migration;

class Install extends Migration
{
    private $seedsTable = '{{%sproutimport_seeds}}';

    public function safeUp()
    {
        $this->createTables();
    }

    public function createTables()
    {
        $seedsTable = $this->getDb()->tableExists($this->seedsTable);

        if ($seedsTable == false) {
            $this->createTable($this->seedsTable,
                [
                    'id' => $this->primaryKey(),
                    'itemId' => $this->integer()->notNull(),
                    'importerClass' => $this->string()->notNull(),
                    'type' => $this->string(),
                    'details' => $this->string(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid()
                ]
            );
        }
    }
}