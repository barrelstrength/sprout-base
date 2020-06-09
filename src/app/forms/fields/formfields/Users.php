<?php

namespace barrelstrength\sproutbase\app\forms\fields\formfields;

use barrelstrength\sproutbase\app\forms\base\FormFieldTrait;
use barrelstrength\sproutbase\app\forms\elements\Entry;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\fields\Users as CraftUsers;
use craft\helpers\Template as TemplateHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;

/**
 * @property string $svgIconPath
 * @property array  $compatibleCraftFields
 * @property array  $compatibleCraftFieldTypes
 * @property mixed  $exampleInputHtml
 */
class Users extends CraftUsers
{
    use FormFieldTrait;

    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @var string
     */
    public $usernameFormat = 'fullName';

    /**
     * @var string Template to use for settings rendering
     */
    protected $settingsTemplate = 'sprout/forms/_components/fields/formfields/users/settings';

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseassets/icons/users.svg';
    }

    /**
     * @inheritdoc
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout/forms/_components/fields/formfields/users/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @param mixed      $value
     * @param Entry      $entry
     * @param array|null $renderingOptions
     *
     * @return Markup
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getFrontEndInputHtml($value, Entry $entry, array $renderingOptions = null): Markup
    {
        $users = SproutBase::$app->frontEndFields->getFrontEndUsers($this->getSettings());

        $rendered = Craft::$app->getView()->renderTemplate('users/input', [
                'name' => $this->handle,
                'value' => $value->ids(),
                'field' => $this,
                'entry' => $entry,
                'renderingOptions' => $renderingOptions,
                'users' => $users,
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    /**
     * @inheritdoc
     */
    public function getCompatibleCraftFieldTypes(): array
    {
        return [
            CraftUsers::class
        ];
    }
}
