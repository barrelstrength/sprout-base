<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbase\migrations;

use craft\db\Migration;

class m200701_000002_update_component_types extends Migration
{
    public function safeUp()
    {
        $components = [
            'sproutreports_datasources' => [
                'type' => [
                    [
                        'oldType' => 'barrelstrength\sproutbasereports\datasources\CustomQuery',
                        'newType' => 'barrelstrength\sproutbase\app\reports\datasources\CustomQuery',
                    ],
                    [
                        'oldType' => 'barrelstrength\sproutbasereports\datasources\CustomTwigTemplate',
                        'newType' => 'barrelstrength\sproutbase\app\reports\datasources\CustomTwigTemplate',
                    ],
                    [
                        'oldType' => 'barrelstrength\sproutbasereports\datasources\Users',
                        'newType' => 'barrelstrength\sproutbase\app\reports\datasources\Users',
                    ],
                ],
            ],
            'sproutlists_lists' => [
                'type' => [
                    [
                        'oldType' => 'barrelstrength\sproutlists\listtypes\SubscriberList',
                        'newType' => 'barrelstrength\sproutbase\app\lists\listtypes\SubscriberList',
                    ],
                    [
                        'oldType' => 'barrelstrength\sproutlists\listtypes\WishList',
                        'newType' => 'barrelstrength\sproutbase\app\lists\listtypes\WishList',
                    ],
                ],
            ],
            'sproutforms_rules' => [
                'type' => [
                    [
                        'oldType' => 'barrelstrength\sproutforms\rules\FieldRule',
                        'newType' => 'barrelstrength\sproutbase\app\forms\rules\FieldRule',
                    ],
                ],
            ],
            'sproutforms_integrations' => [
                'type' => [
                    'oldType' => 'barrelstrength\sproutforms\integrationtypes\EntryElementIntegration',
                    'newType' => 'barrelstrength\sproutbase\app\forms\integrationtypes\EntryElementIntegration',
                ],
                [
                    'oldType' => 'barrelstrength\sproutforms\integrationtypes\CustomEndpoint',
                    'newType' => 'barrelstrength\sproutbase\app\forms\integrationtypes\CustomEndpoint',
                ],
            ],
            'sproutemail_notificationemails' => [
                'emailTemplateId' => [
                    // Email
                    [
                        'oldType' => 'barrelstrength\sproutbaseemail\emailtemplates\BasicTemplates',
                        'newType' => 'barrelstrength\sproutbase\app\email\emailtemplates\BasicTemplates',
                    ],

                    // Forms
                    [
                        'oldType' => 'barrelstrength\sproutforms\integrations\sproutemail\emailtemplates\basic\BasicSproutFormsNotification',
                        'newType' => 'barrelstrength\sproutbase\app\forms\integrations\sproutemail\emailtemplates\basic\BasicSproutFormsNotification',
                    ],
                ],
                'eventId' => [
                    // Email
                    [
                        'oldType' => 'barrelstrength\sproutemail\events\notificationevents\EntriesDelete',
                        'newType' => 'barrelstrength\sproutbase\app\email\events\notificationevents\EntriesDelete',
                    ],
                    [
                        'oldType' => 'barrelstrength\sproutemail\events\notificationevents\EntriesSave',
                        'newType' => 'barrelstrength\sproutbase\app\email\events\notificationevents\EntriesSave',
                    ],
                    [
                        'oldType' => 'barrelstrength\sproutemail\events\notificationevents\Manual',
                        'newType' => 'barrelstrength\sproutbase\app\email\events\notificationevents\Manual',
                    ],
                    [
                        'oldType' => 'barrelstrength\sproutemail\events\notificationevents\UsersActivate',
                        'newType' => 'barrelstrength\sproutbase\app\email\events\notificationevents\UsersActivate',
                    ],
                    [
                        'oldType' => 'barrelstrength\sproutemail\events\notificationevents\UsersDelete',
                        'newType' => 'barrelstrength\sproutbase\app\email\events\notificationevents\UsersDelete',
                    ],
                    [
                        'oldType' => 'barrelstrength\sproutemail\events\notificationevents\UsersLogin',
                        'newType' => 'barrelstrength\sproutbase\app\email\events\notificationevents\UsersLogin',
                    ],
                    [
                        'oldType' => 'barrelstrength\sproutemail\events\notificationevents\UsersSave',
                        'newType' => 'barrelstrength\sproutbase\app\email\events\notificationevents\UsersSave',
                    ],

                    // Forms
                    [
                        'oldType' => 'barrelstrength\sproutforms\integrations\sproutemail\events\notificationevents\SaveEntryEvent',
                        'newType' => 'barrelstrength\sproutbase\app\forms\integrations\sproutemail\events\notificationevents\SaveEntryEvent',
                    ],
                ],
            ],
            'sproutforms_forms' => [
                'formTemplateId' => [
                    [
                        'oldType' => 'barrelstrength\sproutforms\formtemplates\AccessibleTemplates',
                        'newType' => 'barrelstrength\sproutbase\app\forms\formtemplates\AccessibleTemplates',
                    ],

                    // @todo - MOVE TO PLUGIN
//                    [
//                        'oldType' => 'barrelstrength\sproutforms\formtemplates\CustomTemplates',
//                        'newType' => 'barrelstrength\sproutbase\app\forms\formtemplates\CustomTemplates',
//                    ],
                ],
            ],
        ];

        foreach ($components as $dbTableName => $columns) {
            foreach ($columns as $column => $types) {
                foreach ($types as $type) {
                    $this->update('{{%'.$dbTableName.'}}', [
                        $column => $type['newType'],
                    ], [
                        $column => $type['oldType'],
                    ], [], false);
                }
            }
        }
    }

    public function safeDown(): bool
    {
        echo "m200701_000002_update_component_types cannot be reverted.\n";

        return false;
    }
}
