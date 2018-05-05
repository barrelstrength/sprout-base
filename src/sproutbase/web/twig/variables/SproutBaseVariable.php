<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\sproutbase\web\twig\variables;

use barrelstrength\sproutbase\elements\sproutemail\NotificationEmail;
use barrelstrength\sproutbase\integrations\emailtemplates\BasicTemplates;
use barrelstrength\sproutbase\SproutBase;
use Craft;

class SproutBaseVariable
{
    /**
     * @return array
     */
    public function getAvailableEvents()
    {
        return SproutBase::$app->notificationEvents->getNotificationEmailEventTypes();
    }

    /**
     * @param $event
     * @param $notificationEmail
     *
     * @return mixed
     */
    public function prepareEventSettingsForHtml($event, $notificationEmail)
    {
        return SproutBase::$app->notificationEvents->prepareEventSettingsForHtml($event, $notificationEmail);
    }

    public function getNotificationEmailById($id)
    {
        return SproutBase::$app->notifications->getNotificationEmailById($id);
    }

    /**
     * Return countries for Phone Field
     *
     * @return array
     */
    public function getCountries()
    {
        return SproutBase::$app->phone->getCountries();
    }

    /**
     * Get the available Email Template Options
     *
     * @param NotificationEmail|null $notificationEmail
     *
     * @return array
     */
    public function getEmailTemplateOptions($notificationEmail = null)
    {
        $defaultEmailTemplates = new BasicTemplates();
        $templates = SproutBase::$app->template->getAllGlobalTemplates();
        $templateIds = [];
        $options = [
            [
                'label' => Craft::t('sprout-base', 'Select...'),
                'value' => ''
            ]
        ];

        // Build our options
        foreach ($templates as $template) {
            $options[] = [
                'label' => $template->getName(),
                'value' => $template->getTemplateId()
            ];
            $templateIds[] = $template->getTemplateId();
        }

        $templateFolder = null;
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-email');

        if ($plugin) {
            $settings = $plugin->getSettings();
        }

        $templateFolder = $notificationEmail->emailTemplateId ?? $settings->emailTemplateId ?? $defaultEmailTemplates->getPath();

        $options[] = [
            'optgroup' => Craft::t('sprout-base', 'Custom Template Folder')
        ];

        if (!in_array($templateFolder, $templateIds, false) && $templateFolder != '') {
            $options[] = [
                'label' => $templateFolder,
                'value' => $templateFolder
            ];
        }

        $options[] = [
            'label' => Craft::t('sprout-base', 'Add Custom'),
            'value' => 'custom'
        ];

        return $options;
    }
}
