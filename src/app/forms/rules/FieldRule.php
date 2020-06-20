<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\rules;

use barrelstrength\sproutbase\app\forms\base\Rule;
use barrelstrength\sproutbase\app\forms\elements\Form;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Field;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\InvalidConfigException;

class FieldRule extends Rule
{
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Field Rule');
    }

    /**
     * @return string|null
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout/forms/_components/rules/fieldrule/settings',
            [
                'fieldRule' => $this,
            ]
        );
    }

    public function getBehaviorActions(): array
    {
        return [
            'Show',
            'Hide',
        ];
    }

    public function getBehaviorActionsAsOptions(): array
    {
        $options = [];
        foreach ($this->getBehaviorActions() as $behaviorAction) {
            $options[] = [
                'label' => $behaviorAction,
                'value' => strtolower($behaviorAction),
            ];
        }

        return $options;
    }

    /**
     * @throws InvalidConfigException
     */
    public function getRuleTargets(): array
    {
        $fields = $this->getForm()->getFields();
        $rules = [];

        foreach ($fields as $field) {
            $rules[$field->handle]['conditionsAsOptions'] = $field->getConditionsAsOptions();
        }

        return $rules;
    }

    /**
     * @throws InvalidConfigException
     */
    public function getBehaviorDescription(): string
    {
        $behavior = '-';

        if ($this->behaviorAction && $this->behaviorTarget) {
            /** @var Form $form */
            $form = SproutBase::$app->forms->getFormById($this->formId);
            /** @var Field $field */
            $field = $form->getField($this->behaviorTarget);
            if ($field !== null) {
                $behavior = ucwords($this->behaviorAction).' '.$field->name;
            }
        }

        return $behavior;
    }
}

