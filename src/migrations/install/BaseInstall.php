<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\migrations\install;

use barrelstrength\sproutbase\config\base\SproutBasePlugin;
use barrelstrength\sproutbase\SproutBase;
use craft\db\Migration;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class BaseInstall extends Migration
{
    private $plugin;

    public function __construct(SproutBasePlugin $plugin, $config = [])
    {
        $this->plugin = $plugin;

        parent::__construct($config);
    }

    /**
     * @throws ErrorException
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function safeUp()
    {
        SproutBase::$app->config->runInstallMigrations($this->plugin);
    }

    public function safeDown()
    {
        SproutBase::$app->config->runUninstallMigrations($this->plugin);
    }
}
