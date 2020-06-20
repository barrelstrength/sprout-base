<?php

namespace barrelstrength\sproutbase\app\campaigns\models;

use barrelstrength\sproutbase\app\campaigns\elements\CampaignEmail;
use barrelstrength\sproutbase\app\campaigns\records\CampaignType as CampaignTypeRecord;
use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\base\SenderTrait;
use barrelstrength\sproutbase\SproutBase;
use craft\base\Field;
use craft\base\Model;
use craft\behaviors\FieldLayoutBehavior;
use craft\models\FieldLayout;
use craft\records\FieldLayoutField;
use craft\validators\UniqueValidator;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Class CampaignTypeModel
 *
 * @mixin FieldLayoutBehavior
 * @package Craft
 * --
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property string $mailer
 * @property string $titleFormat
 * @property string $urlFormat
 * @property bool $hasUrls
 * @property bool $hasAdvancedTitles
 * @property string $template
 * @property string $templateCopyPaste
 * @property int $fieldLayoutId
 * @property FieldLayout $fieldLayout
 * @property int $emailId
 */
class CampaignType extends Model
{
    use SenderTrait;

    /**
     * @var
     */
    public $saveAsNew;

    /**
     * @var
     */
    public $id;

    /**
     * @var
     */
    public $name;

    /**
     * @var
     */
    public $handle;

    /**
     * @var
     */
    public $mailer;

    /**
     * @var
     */
    public $titleFormat;

    /**
     * @var
     */
    public $urlFormat;

    /**
     * @var
     */
    public $hasUrls;

    /**
     * @var
     */
    public $hasAdvancedTitles;

    /**
     * @var
     */
    public $template;

    /**
     * @var
     */
    public $templateCopyPaste;

    /**
     * @var
     */
    public $fieldLayoutId;

    /**
     * @var
     */
    public $emailId;

    public $emailTemplateId;

    /**
     * @var
     */
    protected $fields;

    /**
     * @return array
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['id'], 'number', 'integerOnly' => true];
        $rules[] = [['name', 'handle'], UniqueValidator::class, 'targetClass' => CampaignTypeRecord::class];
        $rules[] = [['name', 'handle'], 'required'];
        $rules[] = [['name', 'handle'], 'string', 'max' => 255];

        return $rules;
    }

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return [
            'fieldLayout' => [
                'class' => FieldLayoutBehavior::class,
                'elementType' => CampaignEmail::class,
            ],
        ];
    }

    /**
     * @return FieldLayout
     * @throws InvalidConfigException
     */
    public function getFieldLayout(): FieldLayout
    {
        /**
         * @var $behavior FieldLayoutBehavior
         */
        $behavior = $this->getBehavior('fieldLayout');

        return $behavior->getFieldLayout();
    }

    /**
     * Returns the fields associated with this form.
     *
     * @return Field[]
     * @throws InvalidConfigException
     */
    public function getFields(): array
    {
        if ($this->fields !== null) {
            $this->fields = [];

            $fieldLayoutFields = $this->getFieldLayout()->getFields();

            /**
             * @var $fieldLayoutField FieldLayoutField
             */
            foreach ($fieldLayoutFields as $fieldLayoutField) {

                /**
                 * @var Field $field
                 */
                $field = $fieldLayoutField->getField();
                $field->required = $fieldLayoutField->required;
                $this->fields[] = $field;
            }
        }

        return $this->fields;
    }

    /**
     * Sets the fields associated with this form.
     *
     * @param $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return Mailer
     * @throws Exception
     */
    public function getMailer(): Mailer
    {
        return SproutBase::$app->mailers->getMailerByName($this->mailer);
    }
}
