<?php

namespace barrelstrength\sproutbase\app\email\base;

use Craft;
use League\HTMLToMarkdown\HtmlConverter;

/**
 * Class EmailTemplates
 */
abstract class EmailTemplates
{
    /**
     * @var EmailElement
     */
    public $email;

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
     * @var string
     */
    private $_htmlBody;

    /**
     * @var string
     */
    private $_textBody;

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

    /**
     * @param $html
     */
    public function setHtmlBody($html)
    {
        $this->_htmlBody = $html;
    }

    /**
     * @param $text
     */
    public function setTextBody($text)
    {
        $this->_textBody = $text;
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getHtmlBody()
    {
        if (!$this->_htmlBody) {
            $this->processEmailTemplates();
        }

        return $this->_htmlBody;
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getTextBody()
    {
        if (!$this->_textBody) {
            $this->processEmailTemplates();
        }

        return $this->_textBody;
    }

    /**
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    protected function processEmailTemplates()
    {
        $view = Craft::$app->getView();
        $oldTemplatePath = $view->getTemplatesPath();
        $view->setTemplatesPath($this->getPath());

        $htmlEmailTemplate = 'email.html';
        $textEmailTemplate = 'email.txt';

        // Allow other extensions for email.html
        foreach (Craft::$app->getConfig()->getGeneral()->defaultTemplateExtensions as $extension) {
            $templateName = 'email.'.$extension;
            if (Craft::$app->getView()->doesTemplateExist($templateName)) {
                $htmlEmailTemplate = $templateName;
                break;
            }
        }

        $htmlBody = Craft::$app->getView()->renderTemplate($htmlEmailTemplate, [
            'email' => $this->email,
            'object' => $this->email->getEventObject()
        ]);

        $textEmailTemplateExists = Craft::$app->getView()->doesTemplateExist($textEmailTemplate);

        // Converts html body to text email if no .txt
        if ($textEmailTemplateExists) {
            $body = Craft::$app->getView()->renderTemplate($textEmailTemplate, [
                'email' => $this->email,
                'object' => $this->email->getEventObject()
            ]);
        } else {
            $converter = new HtmlConverter([
                'strip_tags' => true
            ]);

            // For more advanced html templates, conversion may be tougher. Minifying the HTML
            // can help and ensuring that content is wrapped in proper tags that adds spaces between
            // things in Markdown, like <p> tags or <h1> tags and not just <td> or <div>, etc.
            $markdown = $converter->convert($htmlBody);

            $body = trim($markdown);
        }

        $view->setTemplatesPath($oldTemplatePath);

        $this->setHtmlBody($htmlBody);
        $this->setTextBody($body);
    }
}
