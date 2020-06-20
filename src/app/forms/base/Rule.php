<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\base;

use barrelstrength\sproutbase\app\forms\elements\Form;
use barrelstrength\sproutbase\SproutBase;
use craft\base\SavableComponent;
use yii\base\InvalidConfigException;

/**
 * Class Rule
 *
 * @property array $conditionRules
 * @property Form $form
 * @property array $behaviorActions
 * @property array $ruleTargets
 * @property string $behaviorDescription
 * @property string $behaviorActionLabel
 */
abstract class Rule extends SavableComponent implements RuleInterface
{
    use RuleTrait;

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return SproutBase::$app->forms->getFormById($this->formId);
    }

    /**
     * @param bool $checkCompatibleConditional
     *
     * @return array
     * @throws InvalidConfigException
     */
    final public function getFormFieldsAsOptions($checkCompatibleConditional = false): array
    {
        $fields = $this->getForm()->getFields();
        $fieldOptions = [];

        foreach ($fields as $field) {
            $row = [
                'label' => $field->name.' ('.$field->handle.')',
                'value' => $field->handle,
            ];

            if ($checkCompatibleConditional) {
                $compatibleConditional = $field->getCompatibleConditions();
                if ($compatibleConditional === null) {
                    $row = [];
                }
            }

            if ($row) {
                $fieldOptions[] = $row;
            }
        }

        return $fieldOptions;
    }

    public function settingsAttributes(): array
    {
        $attributes = parent::settingsAttributes();
        $attributes[] = 'conditions';

        return $attributes;
    }

    public function getBehaviorActions(): array
    {
        return [];
    }

    public function getRuleTargets(): array
    {
        return [];
    }

    public function getBehaviorDescription(): string
    {
        return '';
    }
}

