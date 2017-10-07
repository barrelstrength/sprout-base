<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutcore\contracts\sproutimport;

abstract class BaseFieldImporter extends BaseImporter
{
	protected $id;

	/**
	 * Return the name of a Field from the FieldTypeModel
	 *
	 * @return mixed
	 */
	public function getName()
	{
		return $this->getModel()->displayName();
	}

	/**
	 * @return bool
	 */
	public function isField()
	{
		return true;
	}

	/**
	 * Set our $this->model variable to the FieldModel Class.
	 * Our setModel() Method for Fields will always use FieldModel.
	 *
	 * @param FieldModel $model
	 *
	 * @return null
	 */
	public function setModel($model, $settings = array())
	{
		$this->model = $model;
	}

	/**
	 * Return a new FieldType model for our field
	 *
	 * @return mixed
	 */
	public function getModel()
	{
		$className = $this->getModelName();

		$this->model = $className;

		return new $this->model;
	}

	/**
	 * Return dummy data that can be used to generate fake content for this field type
	 *
	 * @return mixed
	 */
	public abstract function getMockData();

	/**
	 * @todo - clean up, empty method
	 */
	public function save()
	{

	}
}
