<?php

namespace barrelstrength\sproutbase\contracts\sproutemail;

use Craft;

/**
 * Class BaseEmailTemplates
 */
abstract class BaseEmailTemplates
{
    /**
     * The Template ID of the email Templates in the emailat {pluginhandle}-{emailtemplateclassname}
     *
     * @example
     * sproutemails-accessibletemplates
     * sproutemails-basictemplates
     *
     * @var string
     */
    public $templateId;

    /**
     * Generates the Template ID
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
     * The name of your email Templates
     *
     * @return string
     */
    abstract public function getName();

    /**
     * The folder path where your email templates exist
     * 
     * @return string
     */
    abstract public function getPath();

    /**
     * Adds pre-defined options for css classes.
     *
     * These classes will display in the CSS Classes dropdown list on the Field Edit modal
     * for Field Types that support the $cssClasses property.
     *
     * @return array
     */
    public function getCssClassDefaults()
    {
        return [];
    }
}
