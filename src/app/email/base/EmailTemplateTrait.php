<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\email\base;

use barrelstrength\sproutbase\app\email\models\SimpleRecipient;
use barrelstrength\sproutbase\app\email\models\SimpleRecipientList;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Element;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
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
    public function getFieldLayoutTabs(Element $element)
    {
        $tabs = [];

        if ($element->getFieldLayout() !== null) {
            $fieldLayoutTabs = $element->getFieldLayout()->getTabs();

            if (!empty($fieldLayoutTabs)) {
                foreach ($fieldLayoutTabs as $index => $tab) {
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
     * @return bool|null|string
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
            $renderedTemplate = Craft::$app->getView()->renderTemplate($template, $variables);
        } catch (\Exception $e) {
            // Specify template .html if no .txt
            $message = $e->getMessage();

            if ($this->folderPath) {
                $message.= Craft::t('sprout-base', '<br />Folder Path: ' . $this->folderPath);
            }

            SproutBase::error($message);

            SproutBase::$app->emailErrorHelper->addError('template', $message);

            return false;
        }

        return $renderedTemplate;
    }

    public function renderObjectTemplateSafely($string, $object)
    {
        try {
            return Craft::$app->getView()->renderObjectTemplate($string, $object);
        } catch (\Exception $e) {
            SproutBase::$app->emailErrorHelper->addError('template', Craft::t('sprout-base', 'Cannot render template. Check template file and object variables.'));
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

    /**
     * @param string $recipients
     *
     * @return SimpleRecipientList
     */
    public function buildRecipientList($recipients)
    {
        $recipientList = new SimpleRecipientList();

        $recipientArray = explode(',', $recipients);

        $validator = new EmailValidator();
        $multipleValidations = new MultipleValidationWithAnd([
            new RFCValidation(),
            new DNSCheckValidation()
        ]);

        foreach ($recipientArray as $recipient) {
            $recipientModel = new SimpleRecipient();
            $recipientModel->email = trim($recipient);

            if ($validator->isValid($recipientModel->email, $multipleValidations))
            {
                $recipientList->addRecipient($recipientModel);
            }

            $recipientList->addInvalidRecipient($recipientModel);
        }

        return $recipientList;
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
                    $recipientEmail = new SimpleRecipient();
                    $recipientEmail->email = $email;

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

    /**
     * @param       $model
     * @param array $object
     * @param array $modelTemplate
     *
     * @return array
     * @throws \yii\base\Exception
     */
    public function getHtmlBody($model, $object = [], $modelTemplate = null)
    {
        $view = Craft::$app->getView();
        $oldTemplatePath = $view->getTemplatesPath();

        // Gets the emailTemplateId from a model
        $modelTemplate = $modelTemplate ?? $model;
        $emailTemplatePath = SproutBase::$app->sproutEmail->getEmailTemplate($modelTemplate);

        $this->setFolderPath($emailTemplatePath);

        $htmlEmailTemplate = 'email.html';
        $textEmailTemplate = 'email.txt';

        $view->setTemplatesPath($emailTemplatePath);

        $htmlBody = $this->renderSiteTemplateIfExists($htmlEmailTemplate, [
            'email' => $model,
            'object' => $object
        ]);

        $textEmailTemplateExists = Craft::$app->getView()->doesTemplateExist($textEmailTemplate);

        // Converts html body to text email if no .txt
        if ($textEmailTemplateExists) {
            $body = $this->renderSiteTemplateIfExists($textEmailTemplate, [
                'email' => $model,
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

        return ['html' => $htmlBody, 'body' => $body];
    }
}