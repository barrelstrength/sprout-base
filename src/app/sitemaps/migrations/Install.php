<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\sitemaps\migrations;

use barrelstrength\sproutbase\migrations\Install as SproutBaseInstall;
use barrelstrength\sproutbase\records\Settings as SproutBaseSettingsRecord;
use barrelstrength\sproutbase\app\sitemaps\models\Settings as SproutSitemapSettings;
use barrelstrength\sproutbase\app\sitemaps\records\SitemapSection as SitemapSectionRecord;
use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use craft\errors\SiteNotFoundException;
use Throwable;
use yii\base\Exception;

/**
 *
 * @property SproutSitemapSettings $sproutSitemapSettingsModel
 */
class Install extends Migration
{
    /**
     * @var string The database driver to use
     */
    public $driver;

    /**
     * @return bool
     * @throws Throwable
     * @throws SiteNotFoundException
     */
    public function safeUp(): bool
    {
        $this->createTables();
        $this->insertDefaultSettings();

        return true;
    }

    /**
     * @return bool|void
     * @throws Throwable
     */
    public function safeDown()
    {
        // Delete Sitemap Table
        $this->dropTableIfExists(SitemapSectionRecord::tableName());
        $this->removeSharedSettings();
    }

    /**
     * @throws SiteNotFoundException
     * @throws Exception
     */
    public function insertDefaultSettings()
    {
        $settingsRow = (new Query())
            ->select(['*'])
            ->from([SproutBaseSettingsRecord::tableName()])
            ->where(['model' => SproutSitemapSettings::class])
            ->one();

        if ($settingsRow === null) {

            $settings = new SproutSitemapSettings();

            $site = Craft::$app->getSites()->getPrimarySite();
            $settings->siteSettings[$site->id] = $site->id;

            $settingsArray = [
                'model' => SproutSitemapSettings::class,
                'settings' => json_encode($settings->toArray())
            ];

            $this->insert(SproutBaseSettingsRecord::tableName(), $settingsArray);
        }
    }

    public function removeSharedSettings()
    {
        $settingsExist = (new Query())
            ->select(['*'])
            ->from([SproutBaseSettingsRecord::tableName()])
            ->where(['model' => SproutSitemapSettings::class])
            ->exists();

        if ($settingsExist) {
            $this->delete(SproutBaseSettingsRecord::tableName(), [
                'model' => SproutSitemapSettings::class
            ]);
        }
    }

    protected function createTables()
    {
        $migration = new SproutBaseInstall();
        ob_start();
        $migration->safeUp();
        ob_end_clean();

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

            $this->createIndexes();
            $this->addForeignKeys();
        }
    }

    protected function createIndexes()
    {
        $this->createIndex(null, SitemapSectionRecord::tableName(), ['siteId']);
    }

    protected function addForeignKeys()
    {
        $this->addForeignKey(null, SitemapSectionRecord::tableName(), ['siteId'], Table::SITES, ['id'], 'CASCADE', 'CASCADE');
    }
}
