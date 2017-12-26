<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\contracts\sproutimport;

use Craft;
use barrelstrength\sproutimport\SproutImport;
use craft\base\Element;
use craft\base\Model;

/**
 * Class BaseSproutImportElementImporter
 *
 * @package Craft
 */
abstract class BaseElementImporter extends BaseImporter
{
	/**
	 * @inheritdoc BaseImporter::getName()
	 *
	 * @return string
	 */
	public function getName()
	{
		/**
		 * @var $model Element
		 */
		$model = $this->getModel();

		if (!is_object($model))
		{
			return $model . SproutImport::t(" Model definition not found.");
		}

		return $model->displayName();
	}

	/**
	 * @return bool
	 */
	public function isElement()
	{
		return true;
	}

	/**
	 * @return mixed
	 */
	public function getElement()
	{
		$name = $this->getModelName();

		$elementName = Craft::$app->getElements()->getElementTypeByRefHandle($name);

		return new $elementName;
	}

	/**
	 * @param       $model
	 * @param array $settings
	 *
	 * @return mixed
	 */
	public function setModel($model, $settings = array())
	{
		$model = $this->processUpdateElement($model, $settings);

		$authorId = null;

		if (isset($settings['attributes']))
		{
			$attributes = $settings['attributes'];

			$model->setAttributes($attributes, false);

			// Check for email and username values if authorId attribute
			if (isset($attributes['authorId']))
			{
				if ($authorId = $this->getAuthorId($attributes['authorId']))
				{
					$model->authorId = $authorId;
				}
			}

			// Check if we have defaults for any unset attributes
			if (isset($settings['settings']['defaults']))
			{
				$defaults = $settings['settings']['defaults'];

				$attributes = $model->attributes();

				foreach ($attributes as $attribute)
				{
					if (property_exists($model, $attribute) && !empty($model->{$attribute}))
					{
						// Check for email and username values if authorId attribute
						if ($attribute == 'authorId' && isset($defaults['authorId']))
						{
							if ($authorId = $this->getAuthorId($defaults['authorId']))
							{
								$model->authorId = $authorId;
							}

							continue;
						}

						if (isset($defaults[$attribute]))
						{
							$model->{$attribute} = $defaults[$attribute];
						}
					}
				}
			}

			// Check only for models that has authorId attribute.
			if ($authorId == null && in_array('authorId', $model->attributes()))
			{
				$message = SproutImport::t("Could not find Author by ID, Email, or Username.");

				SproutImport::error($message);

				$model->addError('invalid-author', $message);
				$model->addError('invalid-author', $settings);
			}
		}

		if (isset($settings['content']))
		{
			if (!empty($settings['content']['title']))
			{
				$model->title = $settings['content']['title'];
			}

			if (!empty($settings['content']['fields']))
			{
				$fields = $settings['content']['fields'];

				if (!empty($fields))
				{
					$fields = SproutImport::$app->elementImporter->resolveMatrixRelationships($fields);

					$message = [];
					if (!$fields)
					{
						$message['error']  = SproutImport::t("Unable to resolve matrix relationships.");
						$message['fields'] = $fields;

						SproutImport::error($message);
					}
				}

				if (isset($settings['content']['related']) && count($settings['content']['related']))
				{
					$related = $settings['content']['related'];
					$fields = SproutImport::$app->elementImporter->resolveRelationships($related, $fields);

					$message = [];
					if (!$fields)
					{
						$message['error']  = SproutImport::t("Unable to resolve related relationships.");
						$message['fields'] = $fields;

						SproutImport::error($message);
					}
				}

				$fields = ['fields' => $fields];

				// Required to associate fields on the element
				$model->fieldLayoutId = $this->getFieldLayoutId($model);

				Craft::$app->getRequest()->setBodyParams($fields);

				$model->setFieldValuesFromRequest('fields');

				if (isset($settings['content']['title']))
				{
					$model->title = $settings['content']['title'];
				}
			}
		}

		$this->model = $model;

		return $this->model;
	}

	abstract function getFieldLayoutId($model);

	/**
	 * Delete an Element using the Element ID
	 * @param $id
	 *
	 * @return bool
	 * @throws \Throwable
	 */
	public function deleteById($id)
	{
		return Craft::$app->getElements()->deleteElementById($id);
	}

	/**
	 * Determine if we have any elements we should handle before handling the current Element
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	protected function processUpdateElement($model, $settings)
	{
		if (!isset($settings['settings']['updateElement']))
		{
			return $model;
		}

		$updateElement = $settings['settings']['updateElement'];

		$element = SproutImport::$app->elementImporter->getModelByMatches($model, $updateElement);

		if ($element)
		{
			return $element;
		}

		return $model;
	}

	/**
	 * @param $authorId
	 *
	 * @return mixed|null
	 */
	protected function getAuthorId($authorId)
	{
		if (is_int($authorId))
		{
			$userModel = Craft::$app->getUsers()->getUserById($authorId);
		}
		else
		{
			$userModel = Craft::$app->getUsers()->getUserByUsernameOrEmail($authorId);
		}

		return isset($userModel) ? $userModel->id : null;
	}

	/**
	 * @return bool
	 * @throws \Throwable
	 */
	public function save()
	{
		$utilities = SproutImport::$app->utilities;

		try
		{
			return Craft::$app->getElements()->saveElement($this->model);
		}
		catch (\Exception $e)
		{
			SproutImport::error($e->getMessage());

			$utilities->addError('invalid-entry-model', $e->getMessage());

			return false;
		}
	}
}