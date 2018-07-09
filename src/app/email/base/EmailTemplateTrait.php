<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\email\base;

use barrelstrength\sproutbase\app\email\models\EmailTemplate;
use barrelstrength\sproutbase\app\email\models\Message;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use League\HTMLToMarkdown\HtmlConverter;

trait EmailTemplateTrait
{
    /**
     * @var string
     */
    protected $templatesPath;

    /**
     * @var string
     */
    private $folderPath;

    /**
     * Use to show folder path in error modal if invalid template folder is specified.
     *
     * @param $path
     */
    private function setFolderPath($path)
    {
        $this->folderPath = $path;
    }

    /**
     * @param       $template
     * @param array $variables
     *
     * @return null|string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function renderTemplateSafely($template, array $variables = [])
    {
        $renderedTemplate = null;

        // @todo Craft 3 - figure out why this is necessary
        // If a blank template is passed in, Craft renders the index template
        // If a template is set specifically to the value `test` Craft also
        // appears to render the index template.
        if (empty($template)) {
            return $renderedTemplate;
        }

        return Craft::$app->getView()->renderTemplate($template, $variables);
    }

    /**
     * @param $string
     * @param $object
     *
     * @return string
     * @throws \yii\base\Exception
     */
    public function renderObjectTemplateSafely($string, $object)
    {
        return Craft::$app->getView()->renderObjectTemplate($string, $object);
    }

    public function addPlaceholderStyleTags($htmlBody, &$styleTags)
    {
        // Get the style tag
        preg_match_all("/<style\\b[^>]*>(.*?)<\\/style>/s", $htmlBody, $matches);

        if (!empty($matches)) {
            $tags = $matches[0];

            // Temporarily replace with style tags with a random string
            if (!empty($tags)) {
                $i = 0;
                foreach ($tags as $tag) {
                    $key = "<!-- %style$i% -->";

                    $styleTags[$key] = $tag;

                    $htmlBody = str_replace($tag, $key, $htmlBody);

                    $i++;
                }
            }
        }

        return $htmlBody;
    }

    public function removePlaceholderStyleTags($htmlBody, $styleTags)
    {
        if (!empty($styleTags)) {
            foreach ($styleTags as $key => $tag) {
                $htmlBody = str_replace($key, $tag, $htmlBody);
            }
        }

        return $htmlBody;
    }

    /**
     * @param EmailElement $email
     * @param array        $object
     *
     * @return EmailTemplate
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getEmailTemplate(EmailElement $email, $object = [])
    {
        $view = Craft::$app->getView();
        $oldTemplatePath = $view->getTemplatesPath();

        $emailTemplatePath = SproutBase::$app->sproutEmail->getEmailTemplatePath($email);

        $this->setFolderPath($emailTemplatePath);

        $htmlEmailTemplate = 'email.html';
        $textEmailTemplate = 'email.txt';

        $view->setTemplatesPath($emailTemplatePath);

        $htmlBody = $this->renderTemplateSafely($htmlEmailTemplate, [
            'email' => $email,
            'object' => $object
        ]);

        $textEmailTemplateExists = Craft::$app->getView()->doesTemplateExist($textEmailTemplate);

        // Converts html body to text email if no .txt
        if ($textEmailTemplateExists) {
            $body = $this->renderTemplateSafely($textEmailTemplate, [
                'email' => $email,
                'object' => $object
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

        $template = new EmailTemplate();
        $template->htmlBody = $htmlBody;
        $template->textBody = $body;

        return $template;
    }
}