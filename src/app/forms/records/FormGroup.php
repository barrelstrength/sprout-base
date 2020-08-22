<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\records;

use craft\db\ActiveRecord;

/**
 * Class FormGroup record.
 *
 * @property int $id    ID
 * @property string $name  Name
 */
class FormGroup extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sprout_form_groups}}';
    }

}