<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\services;

use craft\base\Component;
use craft\base\ElementInterface;
use craft\base\Field;

class Utilities extends Component
{
    /**
     * Returns current Field Type context to properly get field settings
     *
     * @param Field $field Email Field Object
     * @param ElementInterface $element
     *
     * @return string
     */
    public function getFieldContext(Field $field, ElementInterface $element = null): string
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
    public function isAnyOptionsSelected($options, $value = null): bool
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
    public function formatErrors(): string
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
}

