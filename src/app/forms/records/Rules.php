<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\records;

use craft\db\ActiveRecord;

/**
 * Class Rules record.
 *
 * @property $id
 * @property $formId
 * @property $name
 * @property $type
 * @property $rules
 * @property $behaviorAction
 * @property $behaviorTarget
 * @property $settings
 * @property $enabled
 */
class Rules extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%sprout_forms_rules}}';
    }
}