<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\elements\db;

use barrelstrength\sproutbase\app\forms\elements\Form;
use barrelstrength\sproutbase\app\forms\models\EntryStatus;
use barrelstrength\sproutbase\app\forms\records\Entry as EntryRecord;
use barrelstrength\sproutbase\app\forms\records\EntryStatus as EntryStatusRecord;
use barrelstrength\sproutbase\app\forms\records\Form as FormRecord;
use barrelstrength\sproutbase\SproutBase;
use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use yii\base\InvalidConfigException;

class EntryQuery extends ElementQuery
{
    /**
     * @var int
     */
    public $statusId;

    /**
     * @var string
     */
    public $ipAddress;

    /**
     * @var string
     */
    public $userAgent;

    /**
     * @var int
     */
    public $formId;

    /**
     * @var string
     */
    public $formHandle;

    /**
     * @var string
     */
    public $formName;

    /**
     * @var int
     */
    public $formGroupId;

    public $status = [];

    private $excludeSpam = true;

    public function __construct($elementType, array $config = [])
    {
        // Default orderBy
        if (!isset($config['orderBy'])) {
            $config['orderBy'] = 'sprout_form_entries.id';
        }

        parent::__construct($elementType, $config);
    }

    /**
     * Sets the [[statusId]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function statusId($value): EntryQuery
    {
        $this->statusId = $value;

        return $this;
    }

    /**
     * Sets the [[formId]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function formId($value): EntryQuery
    {
        $this->formId = $value;

        return $this;
    }

    /**
     * Sets the [[formHandle]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function formHandle($value): EntryQuery
    {
        $this->formHandle = $value;
        $form = SproutBase::$app->forms->getFormByHandle($value);
        // To add support to filtering we need to have the formId set.
        if ($form) {
            $this->formId = $form->id;
        }

        return $this;
    }

    /**
     * Sets the [[formName]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function formName($value): EntryQuery
    {
        $this->formName = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('sprout_form_entries');

        // Figure out which content table to use
        $this->contentTable = null;

        if (!$this->formId && $this->id) {
            $formIds = (new Query())
                ->select(['formId'])
                ->distinct()
                ->from([EntryRecord::tableName()])
                ->where(Db::parseParam('id', $this->id))
                ->column();

            $this->formId = count($formIds) === 1 ? $formIds[0] : $formIds;
        }

        if ($this->formId && is_numeric($this->formId)) {
            /** @var Form $form */
            $form = SproutBase::$app->forms->getFormById($this->formId);

            if ($form) {
                $this->contentTable = $form->getContentTable();
            }
        }

        $this->query->select([
            'sprout_form_entries.statusId',
            'sprout_form_entries.formId',
            'sprout_form_entries.ipAddress',
            'sprout_form_entries.userAgent',
            'sprout_form_entries.dateCreated',
            'sprout_form_entries.dateUpdated',
            'sprout_form_entries.uid',
            'sprout_forms.name as formName',
            'sprout_forms.handle as formHandle',
            'sprout_forms.groupId as formGroupId',
            'sprout_form_entries_statuses.handle as statusHandle',
        ]);

        $this->query->innerJoin(FormRecord::tableName().' sprout_forms', '[[sprout_forms.id]] = [[sprout_form_entries.formId]]');
        $this->query->innerJoin(EntryStatusRecord::tableName().' sprout_form_entries_statuses', '[[sprout_form_entries_statuses.id]] = [[sprout_form_entries.statusId]]');

        $this->query->andWhere(Db::parseParam(
            '[[sprout_forms.saveData]]', true
        ));

        $this->subQuery->innerJoin(FormRecord::tableName().' sprout_forms', '[[sprout_forms.id]] = [[sprout_form_entries.formId]]');
        $this->subQuery->innerJoin(EntryStatusRecord::tableName().' sprout_form_entries_statuses', '[[sprout_form_entries_statuses.id]] = [[sprout_form_entries.statusId]]');

        if ($this->formId) {
            $this->subQuery->andWhere(Db::parseParam(
                'sprout_form_entries.formId', $this->formId
            ));
        }

        if ($this->id) {
            $this->subQuery->andWhere(Db::parseParam(
                'sprout_form_entries.id', $this->id
            ));
        }

        if ($this->formHandle) {
            $this->query->andWhere(Db::parseParam(
                'sprout_forms.handle', $this->formHandle
            ));
        }

        if ($this->formName) {
            $this->query->andWhere(Db::parseParam(
                'sprout_forms.name', $this->formName
            ));
        }

        if ($this->statusId) {
            $this->subQuery->andWhere(Db::parseParam(
                'sprout_form_entries.statusId', $this->statusId
            ));
        }

        $spamStatusId = SproutBase::$app->formEntryStatuses->getSpamStatusId();

        // If and ID is being requested directly OR the spam status ID OR
        // the spam status handle is explicitly provided, override the include spam flag
        if ($this->id || $this->statusId === $spamStatusId || $this->status === EntryStatus::SPAM_STATUS_HANDLE) {
            $this->excludeSpam = false;
        }

        if ($this->excludeSpam) {
            $this->subQuery->andWhere(Db::parseParam(
                'sprout_form_entries.statusId', $spamStatusId, '!='
            ));
        }


        return parent::beforePrepare();
    }

    /**
     * @inheritDoc
     */
    protected function statusCondition(string $status)
    {
        return Db::parseParam('sprout_form_entries_statuses.handle', $status);
    }

    /**
     * @throws InvalidConfigException
     */
    protected function customFields(): array
    {
        // This method won't get called if $this->formId isn't set to a single int
        /** @var Form $form */
        $form = SproutBase::$app->forms->getFormById($this->formId);

        return $form->getFields();
    }
}
