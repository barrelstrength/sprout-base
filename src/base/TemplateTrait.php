<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\base;

use barrelstrength\sproutbase\models\sproutemail\EmailMessage;
use barrelstrength\sproutbase\models\sproutemail\SimpleRecipient;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Element;
use craft\helpers\Html;
use craft\mail\Message;
use League\HTMLToMarkdown\HtmlConverter;

trait TemplateTrait
{
    protected $templatesPath = null;

    /**
     * Returns whether or not a site template exists
     *
     * @param $template
     *
     * @return bool
     * @throws \yii\base\Exception
     */
    public function doesSiteTemplateExist($template)
    {
        $path = Craft::$app->getView()->getTemplatesPath();

        Craft::$app->getView()->setTemplatesPath(Craft::$app->getPath()->getSiteTemplatesPath());

        $exists = Craft::$app->getView()->doesTemplateExist($template);

        Craft::$app->getView()->setTemplatesPath($path);

        return $exists;
    }

    /**
     * @param Element $element
     *
     * @return array
     */
    public function getModelTabs(Element $element)
    {
        $tabs = [];

        if (count($element->getFieldLayout()) === 0) {
            $modelTabs = $element->getFieldLayout()->getTabs();

            if (!empty($modelTabs)) {
                foreach ($modelTabs as $index => $tab) {
                    // Do any of the fields on this tab have errors?
                    $hasErrors = false;

                    if ($element->hasErrors()) {
                        foreach ($tab->getFields() as $field) {
                            if ($element->getErrors($field->handle)) {
                                $hasErrors = true;
                                break;
                            }
                        }
                    }

                    $tabs[] = [
                        'label' => Craft::t('sprout-base', $tab->name),
                        'url' => '#tab'.($index + 1),
                        'class' => $hasErrors ? 'error' : null
                    ];
                }
            }
        }

        return $tabs;
    }

    /**
     * @param       $template
     * @param array $variables
     *
     * @return null|string
     * @throws \yii\base\Exception
     */
    public function renderSiteTemplateIfExists($template, array $variables = [])
    {
        $renderedTemplate = null;

        // @todo Craft 3 - figure out why this is necessary
        // If a blank template is passed in, Craft renders the index template
        // If a template is set specifically to the value `test` Craft also
        // appears to render the index template.
        if (empty($template)) {
            return $renderedTemplate;
        }

        try {

            $test = Craft::$app->getView()->getTemplatesPath();

            $renderedTemplate = Craft::$app->getView()->renderTemplate($template, $variables);
        } catch (\Exception $e) {
            // Specify template .html if no .txt
            $message = $e->getMessage();

            if (strpos($template, '.txt') === false) {
                $message = str_replace($template, $template.'.html', $message);
            }

            SproutBase::$app->common->addError('template', $message);

            return false;
        }

        return $renderedTemplate;
    }

    /**
     * @param Message $emailModel
     * @param         $notificationEmail
     * @param null    $object
     *
     * @return EmailMessage
     * @throws \yii\base\Exception
     */
    public function renderEmailTemplates(Message $emailModel, $notificationEmail, $object = null)
    {
        // Render Email Entry fields that have dynamic values
        $subject = $this->renderObjectTemplateSafely($notificationEmail->subjectLine, $object);
        $fromName = $this->renderObjectTemplateSafely($notificationEmail->fromName, $object);
        $fromEmail = $this->renderObjectTemplateSafely($notificationEmail->fromEmail, $object);
        $replyTo = $this->renderObjectTemplateSafely($notificationEmail->replyToEmail, $object);

        $emailTemplatePath = SproutBase::$app->sproutEmail->getEmailTemplate($notificationEmail);

        $htmlEmailTemplate = 'email.html';
        $textEmailTemplate = 'email.txt';


        $view = Craft::$app->getView();
        $oldTemplatePath = $view->getTemplatesPath();

        $view->setTemplatesPath($emailTemplatePath);


        $htmlBody = $this->renderSiteTemplateIfExists($htmlEmailTemplate, [
            'email' => $notificationEmail,
            'object' => $object
        ]);

        $textEmailTemplateExists = Craft::$app->getView()->doesTemplateExist($textEmailTemplate);

        // Converts html body to text email if no .txt
        if ($textEmailTemplateExists) {
            $body = $this->renderSiteTemplateIfExists($textEmailTemplate, [
                'email' => $notificationEmail,
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

        $emailModel->setSubject($subject);
        $emailModel->setFrom([$fromEmail => $fromName]);
        $emailModel->setReplyTo($replyTo);
        $emailModel->setTextBody($body);
        $emailModel->setHtmlBody($htmlBody);

        $styleTags = [];

        $htmlBody = $this->addPlaceholderStyleTags($htmlBody, $styleTags);

        // Some Twig code in our email fields may need us to decode
        // entities so our email doesn't throw errors when we try to
        // render the field objects. Example: {variable|date("Y/m/d")}

        $body = Html::decode($body);
        $htmlBody = Html::decode($htmlBody);

        // Process the results of the template s once more, to render any dynamic objects used in fields
        $body = $this->renderObjectTemplateSafely($body, $object);
        $emailModel->setTextBody($body);

        $htmlBody = $this->renderObjectTemplateSafely($htmlBody, $object);

        $htmlBody = $this->removePlaceholderStyleTags($htmlBody, $styleTags);
        $emailModel->setHtmlBody($htmlBody);

        $attributes = [
            'model' => $emailModel,
            'body' => $body,
            'htmlBody' => $htmlBody
        ];

        $emailMessage = new EmailMessage();

        $emailMessage->setAttributes($attributes, false);

        return $emailMessage;
    }

    public function renderObjectTemplateSafely($string, $object)
    {
        try {
            return Craft::$app->getView()->renderObjectTemplate($string, $object);
        } catch (\Exception $e) {
            SproutBase::$app->common->addError('template', Craft::t('sprout-base', 'Cannot render template. Check template file and object variables.'));
        }

        return null;
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


    public function getValidAndInvalidRecipients($recipients)
    {
        $invalidRecipients = [];
        $validRecipients = [];
        $emails = [];

        if (!empty($recipients)) {
            $recipients = explode(',', $recipients);

            foreach ($recipients as $recipient) {
                $email = trim($recipient);
                $emails[] = $email;

                if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                    $invalidRecipients[] = $email;
                } else {
                    $recipientEmail = SimpleRecipient::create([
                        'email' => $email
                    ]);

                    $validRecipients[] = $recipientEmail;
                }
            }
        }

        return [
            'valid' => $validRecipients,
            'invalid' => $invalidRecipients,
            'emails' => $emails
        ];
    }
}