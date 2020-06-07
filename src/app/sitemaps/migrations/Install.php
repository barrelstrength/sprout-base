<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\sitemaps\migrations;

use barrelstrength\sproutbase\app\sitemaps\records\SitemapSection as SitemapSectionRecord;
use craft\db\Migration;
use craft\db\Table;
use Throwable;

class Install extends Migration
{
    /**
     * @return bool|void
     */
    public function safeUp()
    {
        if (!$this->db->tableExists(SitemapSectionRecord::tableName())) {
            $this->createTable(SitemapSectionRecord::tableName(), [
                'id' => $this->primaryKey(),
                'siteId' => $this->integer()->notNull(),
                'uniqueKey' => $this->string(),
                'urlEnabledSectionId' => $this->integer(),
                'enabled' => $this->boolean()->defaultValue(false),
                'type' => $this->string(),
                'uri' => $this->string(),
                'priority' => $this->decimal(11, 1),
                'changeFrequency' => $this->string(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            $this->createIndex(null, SitemapSectionRecord::tableName(), ['siteId']);
            $this->addForeignKey(null, SitemapSectionRecord::tableName(), ['siteId'], Table::SITES, ['id'], 'CASCADE', 'CASCADE');
        }
    }

    /**
     * @return bool|void
     * @throws Throwable
     */
    public function safeDown()
    {
        $this->dropTableIfExists(SitemapSectionRecord::tableName());
    }
}
