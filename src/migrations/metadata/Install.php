<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\migrations\metadata;

use barrelstrength\sproutbase\app\metadata\records\GlobalMetadata as GlobalMetadataRecord;
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

    /**
     * @throws Throwable
     */
    protected function insertDefaultGlobalMetadata()
    {
        $siteId = Craft::$app->getSites()->currentSite->id;

        $defaultSettings = '{
            "seoDivider":"-",
            "defaultOgType":"website",
            "ogTransform":"sproutSeo-socialSquare",
            "twitterTransform":"sproutSeo-socialSquare",
            "defaultTwitterCard":"summary",
            "appendTitleValueOnHomepage":"",
            "appendTitleValue": ""}
        ';

        $this->insert(GlobalMetadataRecord::tableName(), [
            'siteId' => $siteId,
            'identity' => null,
            'ownership' => null,
            'contacts' => null,
            'social' => null,
            'robots' => null,
            'settings' => $defaultSettings
        ]);
    }
}
