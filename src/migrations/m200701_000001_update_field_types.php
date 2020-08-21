<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbase\migrations;

use craft\db\Migration;

class m200701_000001_update_field_types extends Migration
{
    public function safeUp()
    {
        $types = [
            // SEO
            [
                'oldType' => 'barrelstrength\sproutseo\fields\ElementMetadata',
                'newType' => 'barrelstrength\sproutbase\app\seo\fields\ElementMetadata',
            ],

            // Fields
            [
                'oldType' => 'barrelstrength\sproutfields\fields\Address',
                'newType' => 'barrelstrength\sproutbase\app\fields\fields\Address',
            ],
            [
                'oldType' => 'barrelstrength\sproutfields\fields\Email',
                'newType' => 'barrelstrength\sproutbase\app\fields\fields\Email',
            ],
            [
                'oldType' => 'barrelstrength\sproutfields\fields\Gender',
                'newType' => 'barrelstrength\sproutbase\app\fields\fields\Gender',
            ],
            [
                'oldType' => 'barrelstrength\sproutfields\fields\Name',
                'newType' => 'barrelstrength\sproutbase\app\fields\fields\Name',
            ],
            [
                'oldType' => 'barrelstrength\sproutfields\fields\Phone',
                'newType' => 'barrelstrength\sproutbase\app\fields\fields\Phone',
            ],
            [
                'oldType' => 'barrelstrength\sproutfields\fields\Predefined',
                'newType' => 'barrelstrength\sproutbase\app\fields\fields\Predefined',
            ],
            [
                'oldType' => 'barrelstrength\sproutfields\fields\PredefinedDate',
                'newType' => 'barrelstrength\sproutbase\app\fields\fields\PredefinedDate',
            ],
            [
                'oldType' => 'barrelstrength\sproutfields\fields\RegularExpression',
                'newType' => 'barrelstrength\sproutbase\app\fields\fields\RegularExpression',
            ],
            [
                'oldType' => 'barrelstrength\sproutfields\fields\Template',
                'newType' => 'barrelstrength\sproutbase\app\fields\fields\Template',
            ],
            [
                'oldType' => 'barrelstrength\sproutfields\fields\Url',
                'newType' => 'barrelstrength\sproutbase\app\fields\fields\Url',
            ],

            // Form Fields
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Address',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Address',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Categories',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Categories',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Checkboxes',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Checkboxes',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\CustomHtml',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\CustomHtml',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Date',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Date',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Dropdown',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Dropdown',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Email',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Email',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\EmailDropdown',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\EmailDropdown',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Entries',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Entries',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\FileUpload',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\FileUpload',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Hidden',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Hidden',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Invisible',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Invisible',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\MultipleChoice',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\MultipleChoice',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\MultiSelect',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\MultiSelect',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Name',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Name',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Number',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Number',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\OptIn',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\OptIn',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Paragraph',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Paragraph',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Phone',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Phone',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\PrivateNotes',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\PrivateNotes',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\RegularExpression',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\RegularExpression',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\SectionHeading',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\SectionHeading',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\SingleLine',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\SingleLine',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Tags',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Tags',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Url',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Url',
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\fields\Users',
                'newType' => 'barrelstrength\sproutbase\app\forms\fields\formfields\Users',
            ],
        ];

        foreach ($types as $type) {
            $this->update('{{%fields}}', [
                'type' => $type['newType'],
            ], ['type' => $type['oldType']], [], false);
        }
    }

    public function safeDown(): bool
    {
        echo "m200701_000001_update_field_types cannot be reverted.\n";

        return false;
    }
}
