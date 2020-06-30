<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\migrations\lists;

use barrelstrength\sproutbase\app\lists\records\ListElement as ListElementRecord;
use barrelstrength\sproutbase\app\lists\records\Subscriber as SubscriberRecord;
use barrelstrength\sproutbase\app\lists\records\Subscription as SubscriptionRecord;
use craft\db\Migration;

class Install extends Migration
{
    public function safeUp()
    {
        $this->createTable(ListElementRecord::tableName(),
            [
                'id' => $this->primaryKey(),
                'elementId' => $this->integer()->notNull(),
                'type' => $this->string(),
                'name' => $this->string(),
                'handle' => $this->string(),
                'count' => $this->integer(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]
        );

        $this->createTable(SubscriberRecord::tableName(),
            [
                'id' => $this->primaryKey(),
                'userId' => $this->integer(),
                'email' => $this->string(),
                'firstName' => $this->string(),
                'lastName' => $this->string(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]
        );

        $this->createTable(SubscriptionRecord::tableName(),
            [
                'id' => $this->primaryKey(),
                'listId' => $this->integer(),
                'itemId' => $this->integer(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]
        );
    }

    public function safeDown()
    {
        $this->dropTable(ListElementRecord::tableName());
        $this->dropTable(SubscriberRecord::tableName());
        $this->dropTable(SubscriptionRecord::tableName());
    }
}