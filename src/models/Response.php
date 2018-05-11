<?php

namespace barrelstrength\sproutbase\models;

use barrelstrength\sproutfields\fields\Email;
use craft\base\Model;
use Craft;

/**
 * Class Response
 */
class Response extends Model
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
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public static function createModalResponse($template = '', array $variables = [])
    {
        /** @var $instance Response */
        $instance = get_called_class();
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
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public static function createErrorModalResponse($template = null, array $variables = [])
    {
        /** @var Response $instance */
        $instance = get_called_class();
        $instance = new $instance();

        $instance->success = false;
        $instance->setAttributes($variables, false);

        if ($template && Craft::$app->getView()->doesTemplateExist($template)) {
            $variables = array_merge($variables, $instance->getAttributes());

            $instance->content = Craft::$app->getView()->renderTemplate($template, $variables);
        }

        return $instance;
    }
}
