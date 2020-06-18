<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\migrations\metadata;

use barrelstrength\sproutbase\app\seo\records\GlobalMetadata as GlobalMetadataRecord;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\db\Migration;
use craft\db\Table;
use Throwable;

class Install extends Migration
{
    /**
     * @return bool|void
     * @throws Throwable
     */
    public function safeUp()
    {
        if (!$this->db->tableExists(GlobalMetadataRecord::tableName())) {
            $this->createTable(GlobalMetadataRecord::tableName(), [
                'id' => $this->primaryKey(),
                'siteId' => $this->integer()->notNull(),
                'meta' => $this->text(),
                'identity' => $this->text(),
                'ownership' => $this->text(),
                'contacts' => $this->text(),
                'social' => $this->text(),
                'robots' => $this->text(),
                'settings' => $this->text(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            $this->createIndex(null, GlobalMetadataRecord::tableName(), 'id, siteId', true);
            $this->createIndex(null, GlobalMetadataRecord::tableName(), ['siteId'], true);

            $this->addForeignKey(null, GlobalMetadataRecord::tableName(), ['siteId'], Table::SITES, ['id'], 'CASCADE', 'CASCADE');
        }

        $this->insertDefaultGlobalMetadata();
    }

    public function safeDown()
    {
        // Delete Global Metadata Table
        $this->dropTableIfExists(GlobalMetadataRecord::tableName());
    }

    public function insertDefaultGlobalMetadata()
    {
        $siteIds = Craft::$app->getSites()->allSiteIds;

        foreach ($siteIds as $siteId) {
            SproutBase::$app->globalMetadata->insertDefaultGlobalMetadata($siteId);
        }
    }
}
