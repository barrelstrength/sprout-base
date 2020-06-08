<?php

namespace barrelstrength\sproutbase\app\email\models;

use barrelstrength\sproutbase\app\fields\fields\Email;
use Craft;
use craft\base\Model;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

class ModalResponse extends Model
{
    /**
     * Whether or not the request was successful
     *
     * @var bool
     */
    public $success;

    /**
     * The success or error message
     *
     * @var string
     */
    public $message;

    /**
     * @var Email
     */
    public $emailModel;

    /**
     * Rendered HTML content of body
     *
     * @var string
     */
    public $content;

    /**
     * @param string $template
     * @param array  $variables
     *
     * @return ModalResponse
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public static function createModalResponse($template = '', array $variables = []): ModalResponse
    {
        /** @var $instance ModalResponse */
        $instance = static::class;
        $instance = new $instance();

        $instance->success = true;
        $instance->setAttributes($variables);

        if ($template && Craft::$app->getView()->doesTemplateExist($template)) {
            $variables = array_merge($instance->getAttributes(), $variables);

            $instance->content = Craft::$app->getView()->renderTemplate($template, $variables);
        }

        return $instance;
    }

    /**
     * @param null  $template
     * @param array $variables
     *
     * @return ModalResponse
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public static function createErrorModalResponse($template, array $variables = []): ModalResponse
    {
        /** @var ModalResponse $instance */
        $instance = static::class;
        $instance = new $instance();

        $instance->success = false;
        $instance->setAttributes($variables, false);

        $variables = array_merge($variables, $instance->getAttributes());

        // Ensure we're loading CP templates in case we email template errors tripped up the path
        Craft::$app->getView()->setTemplatesPath(Craft::$app->getPath()->getCpTemplatesPath());

        $instance->content = Craft::$app->getView()->renderTemplate($template, $variables);

        return $instance;
    }
}
