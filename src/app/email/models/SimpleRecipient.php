<?php

namespace barrelstrength\sproutbase\app\email\models;

use craft\base\Model;

/**
 * Represents a simple email recipient
 */
class SimpleRecipient extends Model
{
    /**
     * The name of an email recipient
     *
     * @var string
     */
    public $name;

    /**
     * The email address of an email recipient
     *
     * @var string
     */
    public $email;
}
