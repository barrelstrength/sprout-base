<?php

namespace barrelstrength\sproutbase\app\email\base;

use Craft;

/**
 * Class EmailTemplates
 */
abstract class EmailTemplates
{
    /**
     * The Template ID of the email Templates in the email: {pluginhandle}-{emailtemplateclassname}
     *
     * @example
     * sproutemail-basictemplates
     * sproutforms-basictemplates
     *
     * @var string
     */
    public $templateId;

    /**
     * Generates the Template ID
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getTemplateId()
    {
        $pluginHandle = Craft::$app->getPlugins()->getPluginHandleByClass(get_class($this));

        // Build $templateId: pluginhandle-emailtemplateclassname
        $pluginHandleWithoutSpaces = str_replace('-', '', $pluginHandle);

        $captchaClass = (new \ReflectionClass($this))->getShortName();
        $pluginHandleWithoutSpaces = $pluginHandleWithoutSpaces ?: 'sproutemail';
        $templateId = $pluginHandleWithoutSpaces.'-'.$captchaClass;

        $this->templateId = strtolower($templateId);

        return $this->templateId;
    }

    /**
     * The name of your Email Templates.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * The folder path where your email templates exist
     *
     * This value should be a folder. Sprout Email will look for a required email.html file and an optional email.txt file within this folder.
     * 
     * @return string
     */
    abstract public function getPath();
}
