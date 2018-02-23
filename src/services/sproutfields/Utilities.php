<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services\sproutfields;

use barrelstrength\sproutbase\SproutBase;
use craft\base\Component;
use craft\base\Element;
use Craft;
use yii\base\Exception;

class Utilities extends Component
{
    /**
     * Returns current Field Type context to properly get field settings
     *
     * @param         $field Email Field Object
     * @param Element $element
     *
     * @return string
     */
    public function getFieldContext($field, Element $element)
    {
        $context = 'global';

        if ($field->context) {
            $context = $field->context;
        }

        if ($element) {
            $context = $element->getFieldContext();
        }

        return $context;
    }

    /**
     * @param      $options
     * @param null $value
     *
     * @return bool
     */
    public function isAnyOptionsSelected($options, $value = null)
    {
        if (!empty($options)) {
            foreach ($options as $option) {
                if ($option->selected == true || ($value != null && $value == $option->value)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function formatErrors()
    {
        $errors = $this->getErrors();

        $text = '';
        if (!empty($errors)) {
            $text .= '<ul>';
            foreach ($errors as $key => $error) {
                if (is_array($error)) {
                    foreach ($error as $desc) {
                        $text .= '<li>'.$desc.'</li>';
                    }
                }
            }
            $text .= '</ul>';
        }

        return $text;
    }

    /**
     * @param $fieldPattern
     * @param $element
     *
     * @return string
     * @throws \yii\db\Exception
     */
    public function processPredefinedField($fieldPattern, $element)
    {
        $value = '';

        try {
            $value = Craft::$app->view->renderObjectTemplate($fieldPattern, $element);
        } catch (\Exception $e) {
            SproutBase::error($e->getMessage());
        }

        return $value;
    }
}

