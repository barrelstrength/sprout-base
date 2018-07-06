<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\email\base;


use barrelstrength\sproutemail\elements\CampaignEmail;

use barrelstrength\sproutemail\models\CampaignType;
use craft\base\Element;
use yii\base\Model;
use craft\helpers\UrlHelper;
use Craft;

abstract class Mailer
{
    /**
     * The settings for this mailer
     *
     * @var Model
     */
    protected $settings;

    /**
     * Returns the Mailer Title when used in string context
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * The Mailer Name
     *
     * @example Sprout Email
     * @example AWS
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Returns a short description of this mailer
     *
     * @example The Sprout Email Mailer uses the Craft API to send emails
     *
     * @return string
     */
    abstract public function getDescription();

    /**
     * Returns whether or not the mailer has registered routes to accomplish tasks within Sprout Email
     *
     * @return bool
     */
    public function hasCpSection()
    {
        return false;
    }

    /**
     * Returns whether or not the mailer has settings to display
     *
     * @return bool
     */
    public function hasCpSettings()
    {
        $settings = $this->defineSettings();

        return is_array($settings) && count($settings);
    }

    /**
     * Returns the URL for this Mailer's CP Settings
     *
     * @return null|string
     */
    public function getCpSettingsUrl()
    {
        if (!$this->hasCpSettings()) {
            return null;
        }

        // @todo - getId no longer exists, review
        return UrlHelper::cpUrl('sprout-email/settings/mailers/'.$this->getId());
    }

    /**
     * @todo - do we need to define settings any longer? Or can we just use variables on the specific Mailer Class?
     *
     * Enables mailers to define their own settings and validation for them
     *
     * @return array
     */
    public function defineSettings()
    {
        return [];
    }

    /**
     * Returns the value that should be saved to the settings column for this mailer
     *
     * @example
     * return craft()->request->getPost('sproutemail');
     *
     * @return mixed
     */
    public function prepSettings()
    {
        // @todo - getId no longer exists, review
        return Craft::$app->getRequest()->getParam($this->getId());
    }

    /**
     * Returns the settings model for this mailer
     *
     * @return Model
     */
    public function getSettings()
    {
        $currentPluginHandle = Craft::$app->request->getSegment(1);

        $plugin = Craft::$app->plugins->getPlugin($currentPluginHandle);

        $settings = null;

        if ($plugin) {
            $settings = $plugin->getSettings();
        }

        return $settings;
    }

    /**
     * Returns a rendered html string to use for capturing settings input
     *
     * @param array $settings
     *
     * @return string|Model
     */
    public function getSettingsHtml(array $settings = [])
    {
        return '';
    }

    /**
     * Allow modification of campaignType model before it is saved.
     *
     * @param CampaignType $model
     *
     * @return CampaignType
     */
    public function prepareSave(CampaignType $model)
    {
        return $model;
    }

    /**
     * Gives mailers the ability to include their own modal resources and register their dynamic action handlers
     *
     * @example
     * Mailers should be calling the following functions from within their implementation
     *
     * craft()->templates->includeJs(File|Resource)();
     * craft()->templates->includeCss(File|Resource)();
     *
     * @note
     * To register a dynamic action handler, mailers should listen for sproutEmailBeforeRender
     * $(document).on('sproutEmailBeforeRender', function(e, content) {});
     */
    public function includeModalResources()
    {
    }

    /**
     * Gives a mailer the ability to register an action to post to when a [prepare] modal is launched
     *
     * @return string
     */
    public function getActionForPrepareModal()
    {
        return 'sprout-email/mailer/get-prepare-modal';
    }

    /**
     * @param CampaignEmail $campaignEmail
     * @param CampaignType  $campaignType
     *
     * @return mixed
     */
    abstract public function getPrepareModalHtml(CampaignEmail $campaignEmail, CampaignType $campaignType);

    /**
     * Return true to allow and show mailer dynamic recipients
     *
     * @return bool
     */
    public function hasInlineRecipients()
    {
        return false;
    }

    /**
     * Returns whether this Mailer supports mailing lists
     *
     * @return bool Whether this Mailer supports lists. Default is `true`.
     */
    public function hasLists()
    {
        return true;
    }

    /**
     * Returns the Lists available to this Mailer
     */
    public function getLists()
    {
        return [];
    }

    /**
     * Returns the HTML for our List Settings on the Campaign and Notification Email edit page
     *
     * @param array $values
     *
     * @return null
     */
    public function getListsHtml($values = [])
    {
        return null;
    }

    /**
     * Prepare the list data before we save it in the database
     *
     * @param $lists
     *
     * @return mixed
     */
    public function prepListSettings($lists)
    {
        return $lists;
    }

    /**
     * @param Element $email
     *
     * @return Element
     */
    public function beforeValidate(Element $email)
    {
        return $email;
    }

    /**
     * @param $campaignEmail
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getRecipientsHtml($campaignEmail)
    {
        $defaultFromName = "";
        $defaultFromEmail = "";
        $defaultReplyTo = "";

        return Craft::$app->getView()->renderTemplate('sprout-base-email/_components/mailers/recipients-html',[
            'campaignEmail' => $campaignEmail,
            'defaultFromName' => $defaultFromName,
            'defaultFromEmail' => $defaultFromEmail,
            'defaultReplyTo' => $defaultReplyTo,
        ]);
    }
}