<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\records;

use barrelstrength\sproutbase\app\forms\base\Integration as IntegrationApi;
use craft\db\ActiveRecord;

/**
 * @property $id
 * @property $formId
 * @property $name
 * @property $type
 * @property $sendRule
 * @property $settings
 * @property null|IntegrationApi $integrationApi
 * @property $enabled
 */
class Integration extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%sproutforms_integrations}}';
    }
}