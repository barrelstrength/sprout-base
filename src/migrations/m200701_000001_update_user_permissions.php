<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;

class m200701_000001_update_user_permissions extends Migration
{
    public function safeUp()
    {
        $permissionMap = [
            // Forms
            'forms' => [
                'sproutForms-editForms' => 'sprout:forms:editForms',
                'sproutForms-viewEntries' => 'sprout:forms:viewEntries',
                'sproutForms-editEntries' => 'sprout:forms:editEntries',
            ],

            // Reports
            'reports' => [
                'sproutReports-viewReports' => 'sprout:reports:viewReports',
                'sproutReports-editReports' => 'sprout:reports:editReports',
                'sproutForms-viewReports' => 'sprout:reports:viewReports',
                'sproutForms-editReports' => 'sprout:reports:editReports',
            ],

            // Sent Email
            'sentEmail' => [
                'sproutSentEmail-viewSentEmail' => 'sprout:sentEmail:viewSentEmail',
                'sproutSentEmail-resendEmails' => 'sprout:sentEmail:resendEmails',
                'sproutEmail-viewSentEmail' => 'sprout:sentEmail:viewSentEmail',
                'sproutEmail-resendEmails' => 'sprout:sentEmail:resendEmails',
            ],

            // Email
            'email' => [
                'sproutEmail-viewNotifications' => 'sprout:email:viewNotifications',
                'sproutEmail-editNotifications' => 'sprout:email:editNotifications',
                'sproutForms-viewNotifications' => 'sprout:email:viewNotifications',
                'sproutForms-editNotifications' => 'sprout:email:editNotifications',
            ],

            // Redirects
            'redirects' => [
                'sproutRedirects-editRedirects' => 'sprout:redirects:editRedirects',
                'sproutSeo-editRedirects' => 'sprout:redirects:editRedirects',
            ],

            // Sitemaps
            'sitemaps' => [
                'sproutSitemaps-editSitemaps' => 'sprout:sitemaps:editSitemaps',
                'sproutSeo-editSitemaps' => 'sprout:sitemaps:editSitemaps',
            ],

            // SEO
            'seo' => [
                'sproutSeo-editGlobals' => 'sprout:seo:editGlobals',
            ],
        ];

        $permissions = (new Query())
            ->select([
                'id',
            ])
            ->from([Table::USERPERMISSIONS])
            ->where([
                'like', 'name', 'sprout%', false,
            ])
            ->indexBy('name')
            ->column();

        foreach ($permissionMap as $pluginHandle => $permissionSet) {

            // Update Permission Names in db
            foreach ($permissionSet as $oldPermissionName => $newPermissionName) {

                $lowerCasePermissionName = strtolower($oldPermissionName);
                $permissionId = $permissions[$lowerCasePermissionName] ?? null;

                if (!$permissionId) {
                    continue;
                }

                // Update permission names one by one so we can also add accessModule permissions
                $this->update(Table::USERPERMISSIONS, [
                    'name' => $newPermissionName,
                ], ['id' => $permissionId], [], false);
            }

            // Add accessModule permissions
            $this->insert(Table::USERPERMISSIONS, [
                'name' => 'sprout:'.$pluginHandle.':accessModule',
            ]);
            $accessModulePermissionId = $this->db->getLastInsertID(Table::USERPERMISSIONS);

            $accessPluginPermissionId = (new Query())
                ->select(['id'])
                ->from([Table::USERPERMISSIONS])
                ->where([
                    'name' => 'accessplugin-sprout-'.$pluginHandle,
                ])
                ->scalar();

            // Add accessModule permission to appropriate userpermissions_usergroups table
            // for any permissions applied to a group
            $accessPluginUserGroupIds = (new Query())
                ->select(['groupId'])
                ->from([Table::USERPERMISSIONS_USERGROUPS])
                ->where([
                    'permissionId' => $accessPluginPermissionId,
                ])
                ->column();

            // Assign the new permissions to the groups
            if (!empty($accessPluginUserGroupIds)) {
                $data = [];
                foreach ($accessPluginUserGroupIds as $groupId) {
                    $data[] = [$accessModulePermissionId, $groupId];
                }
                $this->batchInsert(Table::USERPERMISSIONS_USERGROUPS, ['permissionId', 'groupId'], $data);
            }

            // Add accessModule permission to appropriate userpermissions_users table
            // for any permissions applied to a user
            $accessPluginUserIds = (new Query())
                ->select(['userId'])
                ->from([Table::USERPERMISSIONS_USERS])
                ->where([
                    'permissionId' => $accessPluginPermissionId,
                ])
                ->column();

            // Assign the new permissions to the users
            if (!empty($accessPluginUserIds)) {
                $data = [];
                foreach ($accessPluginUserIds as $userId) {
                    $data[] = [$accessModulePermissionId, $userId];
                }
                $this->batchInsert(Table::USERPERMISSIONS_USERS, ['permissionId', 'userId'], $data);
            }

            // Remove access plugin permission that is no longer in use
            $this->delete(Table::USERPERMISSIONS, [
                'id' => $accessPluginPermissionId,
            ]);
        }
    }

    public function safeDown(): bool
    {
        echo "m200701_000001_update_user_permissions cannot be reverted.\n";

        return false;
    }
}
