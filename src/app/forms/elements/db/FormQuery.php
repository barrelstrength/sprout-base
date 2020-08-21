<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\elements\db;

use barrelstrength\sproutbase\app\forms\models\FormGroup;
use barrelstrength\sproutbase\app\forms\records\Entry as EntryRecord;
use barrelstrength\sproutbase\app\forms\records\Form as FormRecord;
use barrelstrength\sproutbase\app\forms\records\FormGroup as FormGroupRecord;
use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\SproutBase;
use craft\db\Query;
use craft\db\Table;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class FormQuery extends ElementQuery
{

    /**
     * @var int|int[]|null The tag group ID(s) that the resulting forms must be in.
     */
    public $groupId;

    /**
     * @var int
     */
    public $fieldLayoutId;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $handle;

    /**
     * @var string
     */
    public $oldHandle;

    /**
     * @var string
     */
    public $titleFormat;

    /**
     * @var bool
     */
    public $displaySectionTitles;

    /**
     * @var string
     */
    public $redirectUri;

    /**
     * @var string
     */
    public $submissionMethod;

    /**
     * @var string
     */
    public $errorDisplayMethod;

    /**
     * @var string
     */
    public $successMessage;

    /**
     * @var string
     */
    public $errorMessage;

    /**
     * @var string
     */
    public $submitButtonText;

    /**
     * @var bool
     */
    public $saveData;

    /**
     * @var string
     */
    public $formTemplate;

    /**
     * @var bool
     */
    public $enableCaptchas;

    /**
     * @var int
     */
    public $totalEntries;

    /**
     * @var int
     */
    public $numberOfFields;

    /**
     * @inheritDoc
     */
    public function __construct($elementType, array $config = [])
    {
        // Default orderBy
        if (!isset($config['orderBy'])) {
            $config['orderBy'] = 'sprout_forms.name';
        }

        parent::__construct($elementType, $config);
    }

    public function group($value): FormQuery
    {
        if ($value instanceof FormGroup) {
            $this->groupId = $value->id;
        } else if ($value !== null) {
            $this->groupId = (new Query())
                ->select(['id'])
                ->from([FormGroupRecord::tableName()])
                ->where(Db::parseParam('name', $value))
                ->column();
        } else {
            $this->groupId = null;
        }

        return $this;
    }

    /**
     * Sets the [[groupId]] property.
     *
     * @param int|int[]|null $value The property value
     *
     * @return static self reference
     */
    public function groupId($value): FormQuery
    {
        $this->groupId = $value;

        return $this;
    }

    /**
     * Sets the [[name]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function name($value): FormQuery
    {
        $this->name = $value;

        return $this;
    }

    /**
     * Sets the [[handle]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function handle($value): FormQuery
    {
        $this->handle = $value;

        return $this;
    }

    /**
     * @param $value
     *
     * @return FormQuery
     */
    public function fieldLayoutId($value): FormQuery
    {
        $this->fieldLayoutId = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function beforePrepare(): bool
    {
        // See if 'group' was set to an invalid handle
        if ($this->groupId === []) {
            return false;
        }

        $this->joinElementTable('sprout_forms');

        $this->query->select([
            'sprout_forms.groupId',
            'sprout_forms.id',
            'sprout_forms.fieldLayoutId',
            'sprout_forms.groupId',
            'sprout_forms.name',
            'sprout_forms.handle',
            'sprout_forms.titleFormat',
            'sprout_forms.displaySectionTitles',
            'sprout_forms.redirectUri',
            'sprout_forms.saveData',
            'sprout_forms.submissionMethod',
            'sprout_forms.errorDisplayMethod',
            'sprout_forms.successMessage',
            'sprout_forms.errorMessage',
            'sprout_forms.submitButtonText',
            'sprout_forms.formTemplateId',
            'sprout_forms.enableCaptchas',
        ]);

        if ($this->totalEntries) {
            $this->query->addSelect('COUNT(entries.id) totalEntries');
            $this->query->leftJoin(EntryRecord::tableName().' entries', '[[entries.formId]] = [[sprout_forms.id]]');
        }

        if ($this->numberOfFields) {
            $this->query->addSelect('COUNT(fields.id) numberOfFields');
            $this->query->leftJoin(Table::FIELDLAYOUTFIELDS.' fields', '[[fields.layoutId]] = [[sprout_forms.fieldLayoutId]]');
        }

        if ($this->fieldLayoutId) {
            $this->subQuery->andWhere(Db::parseParam(
                'sprout_forms.fieldLayoutId', $this->fieldLayoutId
            ));
        }

        if ($this->groupId) {
            $this->subQuery->andWhere(Db::parseParam(
                'sprout_forms.groupId', $this->groupId
            ));
        }

        if ($this->handle) {
            $this->subQuery->andWhere(Db::parseParam(
                'sprout_forms.handle', $this->handle
            ));
        }

        if ($this->name) {
            $this->subQuery->andWhere(Db::parseParam(
                'sprout_forms.name', $this->name
            ));
        }

        $isPro = SproutBase::$app->config->isEdition('forms', Config::EDITION_PRO);

        // Limit Sprout Forms Lite to a single form
        if (!$isPro) {
            $this->query->limit(1);
        }

        return parent::beforePrepare();
    }
}
