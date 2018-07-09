<?php

namespace barrelstrength\sproutbase\app\email\models;

class EmailTemplate extends \craft\mail\Message
{
    /**
     * @var string
     */
    public $textBody;

    /**
     * @var string
     */
    public $htmlBody;
}