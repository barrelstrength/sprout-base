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
     * @param $field
     * @param $element Element
     *
     * @return string
     * @throws \yii\db\Exception
     */
    public function processPredefinedField($field, $element)
    {
        $value = '';

        try {
            $value = Craft::$app->view->renderObjectTemplate($field->fieldFormat, $element);
        } catch (\Exception $e) {
            SproutBase::error($e->getMessage());
        }

        $fieldColumnPrefix = $element->getFieldColumnPrefix();
        $column = $fieldColumnPrefix.$field->handle;

        Craft::$app->db->createCommand()->update($element->contentTable, [
            $column => $value,
        ], 'elementId=:elementId AND siteId=:siteId', [
            ':elementId' => $element->id,
            ':siteId' => $element->siteId
        ])
            ->execute();
    }
}

