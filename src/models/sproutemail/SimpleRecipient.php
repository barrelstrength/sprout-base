<?php

namespace barrelstrength\sproutbase\models\sproutemail;

use craft\base\Model;

/**
 * Class SproutEmail_SimpleRecipientModel
 *
 * @package Craft
 *
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 */
class SimpleRecipient extends Model
{
	public $firstName;
	public $lastName;
	public $email;

	/**
	 * @param array $attributes
	 *
	 * @return SimpleRecipient
	 */
	public static function create(array $attributes = array())
	{
		$self = new self;

		$self->setAttributes($attributes, false);

		return $self;
	}
}
