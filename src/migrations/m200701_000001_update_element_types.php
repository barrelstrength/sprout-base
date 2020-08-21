<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbase\migrations;

use craft\db\Migration;

class m200701_000001_update_element_types extends Migration
{
    public function safeUp()
    {
        $types = [
            // Email
            [
                'oldType' => 'barrelstrength\sproutbaseemail\elements\NotificationEmail',
                'newType' => 'barrelstrength\sproutbase\app\email\elements\NotificationEmail'
            ],
            [
                'oldType' => 'barrelstrength\sproutbasecampaigns\elements\CampaignEmail',
                'newType' => 'barrelstrength\sproutbase\app\campaigns\elements\CampaignEmail'
            ],
            [
                'oldType' => 'barrelstrength\sproutbasesentemail\elements\SentEmail',
                'newType' => 'barrelstrength\sproutbase\app\sentemail\elements\SentEmail'
            ],
            [
                'oldType' => 'barrelstrength\sproutbasecampaigns\mailers\CopyPasteMailer',
                'newType' => 'barrelstrength\sproutbase\app\campaigns\mailers\CopyPasteMailer'
            ],

            // Forms
            [
                'oldType' => 'barrelstrength\sproutforms\elements\Form',
                'newType' => 'barrelstrength\sproutbase\app\forms\elements\Form'
            ],
            [
                'oldType' => 'barrelstrength\sproutforms\elements\Entry',
                'newType' => 'barrelstrength\sproutbase\app\forms\elements\Entry'
            ],

            // Lists
            [
                'oldType' => 'barrelstrength\sproutlists\elements\ListElement',
                'newType' => 'barrelstrength\sproutbase\app\lists\elements\ListElement'
            ],
            [
                'oldType' => 'barrelstrength\sproutlists\elements\Subscriber',
                'newType' => 'barrelstrength\sproutbase\app\lists\elements\Subscriber'
            ],

            // Redirects
            [
                'oldType' => 'barrelstrength\sproutbaseredirects\elements\Redirect',
                'newType' => 'barrelstrength\sproutbase\app\redirects\elements\Redirect'
            ],

            // Reports
            [
                'oldType' => 'barrelstrength\sproutbasereports\elements\Report',
                'newType' => 'barrelstrength\sproutbase\app\reports\elements\Report'
            ],
        ];

        foreach ($types as $type) {
            $this->update('{{%elements}}', [
                'type' => $type['newType']
            ], ['type' => $type['oldType']], [], false);
        }
    }

    public function safeDown(): bool
    {
        echo "m200701_000001_update_element_types cannot be reverted.\n";

        return false;
    }
}
