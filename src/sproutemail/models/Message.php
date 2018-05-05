<?php

namespace barrelstrength\sproutbase\sproutemail\models;

/**
 * Class Message
 *
 * The Message Class adds two variables to help us track the rendered
 * HTML and Text versions of our emails so we can use that data for
 * Sent Email Elements.
 */
class Message extends \craft\mail\Message
{
    /**
     * @var string
     */
    public $renderedBody;

    /**
     * @var string
     */
    public $renderedHtmlBody;
}