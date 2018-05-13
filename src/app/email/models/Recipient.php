<?php

namespace barrelstrength\sproutbase\app\email\models;

use craft\base\Model;

/**
 * Recipient
 *
 * @package Craft
 *
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 */
class Recipient extends Model
{
    public $firstName;
    public $lastName;
    public $email;

    /**
     * @param array $attributes
     *
     * @return Recipient
     */
    public static function create(array $attributes = [])
    {
        $self = new self;

        $self->setAttributes($attributes, false);

        return $self;
    }
}
