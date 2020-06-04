<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\redirects\migrations;

use barrelstrength\sproutbase\migrations\Install as SproutBaseInstall;
use barrelstrength\sproutbase\app\redirects\elements\Redirect;
use barrelstrength\sproutbase\app\redirects\records\Redirect as RedirectRecord;
use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use craft\errors\StructureNotFoundException;
use craft\models\Structure;
use Throwable;
use yii\base\Exception;

/**
 *
 * @property SproutRedirectsSettings $sproutRedirectsSettingsModel
 * @property null|int                $structureId
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
     */
    public function safeUp(): bool
    {
        $this->createTables();
        $this->insertDefaultSettings();

        return true;
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

        // Delete Redirect Settings
        $this->removeSharedSettings();

        return true;
    }

    /**
     * @throws StructureNotFoundException
     * @throws Exception
     */
    public function insertDefaultSettings()
    {
        $settingsRow = (new Query())
            ->select(['*'])
            ->from([SproutBaseSettingsRecord::tableName()])
            ->where(['model' => SproutRedirectsSettings::class])
            ->one();

        if ($settingsRow === null) {

            $settings = new SproutRedirectsSettings();
            $settings->structureId = $this->createStructureId();

            $settingsArray = [
                'model' => SproutRedirectsSettings::class,
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
            ->where(['model' => SproutRedirectsSettings::class])
            ->exists();

        if ($settingsExist) {
            $this->delete(SproutBaseSettingsRecord::tableName(), [
                'model' => SproutRedirectsSettings::class
            ]);
        }
    }

    /**
     */
    protected function createTables()
    {
        $migration = new SproutBaseInstall();
        ob_start();
        $migration->safeUp();
        ob_end_clean();

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

            $this->createIndexes();
            $this->addForeignKeys();
        }
    }

    protected function createIndexes()
    {
        $this->createIndex(null, RedirectRecord::tableName(), 'id');
    }

    protected function addForeignKeys()
    {
        $this->addForeignKey(
            null,
            RedirectRecord::tableName(), 'id',
            Table::ELEMENTS, 'id', 'CASCADE'
        );
    }

    /**
     * @return int|null
     * @throws StructureNotFoundException
     */
    private function createStructureId()
    {
        $maxLevels = 1;
        $structure = new Structure();
        $structure->maxLevels = $maxLevels;
        Craft::$app->structures->saveStructure($structure);

        return $structure->id;
    }

}
