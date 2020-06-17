<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\models;

use barrelstrength\sproutbase\app\forms\base\Integration;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Model;
use craft\errors\MissingComponentException;
use yii\base\InvalidConfigException;

/**
 *
 * @property Integration|null $integration
 */
class IntegrationLog extends Model
{
    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var int|null
     */
    public $entryId;

    /**
     * @var int
     */
    public $integrationId;

    /**
     * @var bool
     */
    public $success;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $dateCreated;

    /**
     * @var string
     */
    public $dateUpdated;

    /**
     * @var string
     */
    public $uid;

    /**
     * Use the translated section name as the string representation.
     *
     * @inheritdoc
     */
    public function __toString()
    {
        return Craft::t('sprout', $this->id);
    }

    /**
     * @return Integration|null
     * @throws MissingComponentException
     * @throws InvalidConfigException
     */
    public function getIntegration()
    {
        return SproutBase::$app->formIntegrations->getIntegrationById($this->integrationId);
    }
}