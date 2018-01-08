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
use craft\base\Model;
use craft\helpers\Html;
use craft\mail\Message;

trait TemplateTrait
{
	/**
	 * Returns whether or not a site template exists
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
	 * @param Model $model
	 *
	 * @return array
	 */
	public function getModelTabs(Model $model)
	{
		$tabs = array();
		/**
		 * @var $model Model
		 */
		if (!empty($model->getFieldLayout()))
		{
			$modelTabs = $model->getFieldLayout()->getTabs();

			if (!empty($modelTabs))
			{
				foreach ($modelTabs as $index => $tab)
				{
					// Do any of the fields on this tab have errors?
					$hasErrors = false;

					if ($model->hasErrors())
					{
						foreach ($tab->getFields() as $field)
						{
							if ($model->getErrors($field->handle))
							{
								$hasErrors = true;
								break;
							}
						}
					}

					$tabs[] = array(
						'label' => Craft::t('sprout-base', $tab->name),
						'url'   => '#tab' . ($index + 1),
						'class' => ($hasErrors ? 'error' : null)
					);
				}
			}
		}

		return $tabs;
	}


	public function renderSiteTemplateIfExists($template, array $variables = array())
	{
		$renderedTemplate = null;

		// @todo Craft 3 - figure out why this is necessary
		// If a blank template is passed in, Craft renders the index template
		// If a template is set specifically to the value `test` Craft also
		// appears to render the index template.
		if (empty($template))
		{
			return $renderedTemplate;
		}

		$oldPath = Craft::$app->getView()->getTemplatesPath();

		Craft::$app->getView()->setTemplatesPath(Craft::$app->getPath()->getSiteTemplatesPath());

		try
		{
			$renderedTemplate = Craft::$app->getView()->renderTemplate($template, $variables);
		}
		catch (\Exception $e)
		{
			// Specify template .html if no .txt
			$message = $e->getMessage();

			if (strpos($template, '.txt') === false)
			{
				$message = str_replace($template, $template . '.html', $message);
			}

			SproutBase::$app->utilities->addError('template', $message);
		}

		Craft::$app->getView()->setTemplatesPath($oldPath);

		return $renderedTemplate;
	}

	public function renderEmailTemplates(Message $emailModel, $template, $notification, $object = null)
	{
		// Render Email Entry fields that have dynamic values
		$subject   = $this->renderObjectTemplateSafely($notification->subjectLine, $object);
		$fromName  = $this->renderObjectTemplateSafely($notification->fromName, $object);
		$fromEmail = $this->renderObjectTemplateSafely($notification->fromEmail, $object);
		$replyTo   = $this->renderObjectTemplateSafely($notification->replyToEmail, $object);
		$body      = $this->renderSiteTemplateIfExists($template . '.txt', array(
			'email'  => $notification,
			'object' => $object
		));

		$htmlBody = $this->renderSiteTemplateIfExists($template, array(
			'email'  => $notification,
			'object' => $object
		));

		$emailModel->setSubject($subject);

		$emailModel->setFrom([$fromEmail => $fromName]);

		$emailModel->setReplyTo($replyTo);

		$emailModel->setTextBody($body);

		$emailModel->setHtmlBody($htmlBody);

		$styleTags = array();

		$htmlBody = $this->addPlaceholderStyleTags($htmlBody, $styleTags);

		// Some Twig code in our email fields may need us to decode
		// entities so our email doesn't throw errors when we try to
		// render the field objects. Example: {variable|date("Y/m/d")}

		$body     = Html::decode($body);
		$htmlBody = Html::decode($htmlBody);

		// Process the results of the template s once more, to render any dynamic objects used in fields
		$body = $this->renderObjectTemplateSafely($body, $object);
		$emailModel->setTextBody($body);

		$htmlBody = $this->renderObjectTemplateSafely($htmlBody, $object);

		$htmlBody = $this->removePlaceholderStyleTags($htmlBody, $styleTags);
		$emailModel->setHtmlBody($htmlBody);

		$attributes = [
			'model'    => $emailModel,
			'body'     => $body,
			'htmlBody' => $htmlBody
		];

		$emailMessage = new EmailMessage();

		$emailMessage->setAttributes($attributes, false);

		return $emailMessage;
	}

	public function renderObjectTemplateSafely($string, $object)
	{
		try
		{
			return Craft::$app->getView()->renderObjectTemplate($string, $object);
		}
		catch (\Exception $e)
		{
			SproutBase::$app->utilities->addError('template', Craft::t('sprout-email', 'Cannot render template. Check template file and object variables.'));
		}

		return null;
	}

	public function addPlaceholderStyleTags($htmlBody, &$styleTags)
	{
		// Get the style tag
		preg_match_all("/<style\\b[^>]*>(.*?)<\\/style>/s", $htmlBody, $matches);

		if (!empty($matches))
		{
			$tags = $matches[0];

			// Temporarily replace with style tags with a random string
			if (!empty($tags))
			{
				$i = 0;
				foreach ($tags as $tag)
				{
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
		if (!empty($styleTags))
		{
			foreach ($styleTags as $key => $tag)
			{
				$htmlBody = str_replace($key, $tag, $htmlBody);
			}
		}

		return $htmlBody;
	}


	public function getValidAndInvalidRecipients($recipients)
	{
		$invalidRecipients = array();
		$validRecipients   = array();
		$emails            = array();

		if (!empty($recipients))
		{
			$recipients = explode(",", $recipients);

			foreach ($recipients as $recipient)
			{
				$email    = trim($recipient);
				$emails[] = $email;

				if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
				{
					$invalidRecipients[] = $email;
				}
				else
				{
					$recipientEmail = SimpleRecipient::create(array(
						'email' => $email
					));

					$validRecipients[] = $recipientEmail;
				}
			}
		}

		return array(
			'valid'   => $validRecipients,
			'invalid' => $invalidRecipients,
			'emails'  => $emails
		);
	}
}