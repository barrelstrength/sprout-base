<?php

namespace barrelstrength\sproutbase\app\email\models;

use barrelstrength\sproutfields\fields\Email;
use craft\base\Model;
use Craft;

/**
 * Class Response
 */
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
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
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
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public static function createErrorModalResponse($template = null, array $variables = []): ModalResponse
    {
        /** @var ModalResponse $instance */
        $instance = static::class;
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
