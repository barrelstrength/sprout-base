<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\elements;

use barrelstrength\sproutbase\app\lists\base\ListInterface;
use barrelstrength\sproutbase\app\lists\base\ListType;
use barrelstrength\sproutbase\app\lists\elements\actions\DeleteList;
use barrelstrength\sproutbase\app\lists\elements\db\ListElementQuery;
use barrelstrength\sproutbase\app\lists\models\Subscription;
use barrelstrength\sproutbase\app\lists\records\ListElement as ListsRecord;
use barrelstrength\sproutbase\app\lists\records\Subscription as SubscriptionRecord;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Element;
use craft\db\Query;
use craft\elements\db\ElementQueryInterface;
use craft\errors\ElementNotFoundException;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use craft\validators\SlugValidator;
use craft\validators\UniqueValidator;
use Exception;
use yii\base\InvalidConfigException;
use yii\web\ErrorHandler;

/**
 * @property mixed $listType
 */
class ListElement extends Element implements ListInterface
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $elementId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $handle;

    /**
     * @var int
     */
    public $count = 0;

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout', 'List');
    }

    /**
     * @return string
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('sprout', 'Lists');
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @return ListElementQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new ListElementQuery(static::class);
    }

    public static function defaultTableAttributes(string $source): array
    {
        return [
            'name',
            'type',
            'elementId',
            'handle',
            'count'
        ];
    }

    /**
     * @param string|null $context
     *
     * @return array
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('sprout', 'All lists')
            ]
        ];

        $listTypes = SproutBase::$app->lists->getAllListTypes();

        if (!empty($listTypes)) {

            $sources[] = [
                'heading' => 'List Types'
            ];

            foreach ($listTypes as $listType) {
                $source = [
                    'key' => 'lists:'.$listType->getClassName(),
                    'label' => $listType::displayName(),
                    'data' => ['type' => $listType->getClassName()],
                    'criteria' => ['type' => $listType->getClassName()]
                ];

                $sources[] = $source;
            }
        }

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'name' => ['label' => Craft::t('sprout', 'Name')],
            'count' => ['label' => Craft::t('sprout', 'Count')],
            'dateCreated' => ['label' => Craft::t('sprout', 'Date Created')]
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'name' => ['label' => Craft::t('sprout', 'Name')],
            'type' => ['label' => Craft::t('sprout', 'List Type')],
            'id' => ['label' => Craft::t('sprout', 'List ID')],
            'elementId' => ['label' => Craft::t('sprout', 'Element ID')],
            'handle' => ['label' => Craft::t('sprout', 'List Handle')],
            'view' => ['label' => Craft::t('sprout', 'View Subscribers')],
            'count' => ['label' => Craft::t('sprout', 'Count')],
            'dateCreated' => ['label' => Craft::t('sprout', 'Date Created')]
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        $actions[] = DeleteList::class;

        return $actions;
    }

    /**
     * Use the name as the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return (string)$this->name;
        } catch (Exception $e) {
            ErrorHandler::convertExceptionToError($e);
        }

        return parent::__toString();
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl('sprout/lists/edit/'.$this->id);
    }

    /**
     * @return ListType
     */
    public function getType(): ListType
    {
        return new $this->type();
    }

    /**
     * @param $criteria
     *
     * @return bool
     */
    public function hasItem($criteria): bool
    {
        $listType = $this->getType();

        // Always use the List ID of the current list
        $criteria['listId'] = $this->id;

        /** @var Subscription $subscription */
        $subscription = $listType->populateSubscriptionFromCriteria($criteria);
        $subscriberOrItem = $listType->getSubscriberOrItem($subscription);

        if (!$subscriberOrItem) {
            return false;
        }

        $subscriptionExists = (new Query())
            ->select(['id'])
            ->from([SubscriptionRecord::tableName()])
            ->where([
                'listId' => $this->id,
                'itemId' => $subscriberOrItem->getId()
            ])
            ->exists();

        return $subscriptionExists;
    }

    /**
     * @param $criteria
     *
     * @return bool
     */
    public function isSubscribed($criteria): bool
    {
        return $this->hasItem($criteria);
    }

    /**
     * @param string $attribute
     *
     * @return string
     */
    public function getTableAttributeHtml(string $attribute): string
    {
        $count = $this->count;

        switch ($attribute) {
            case 'type':
                /** @var ListType $type */
                $type = new $this->type;

                return $type::displayName();
                break;

            case 'handle':
                return '<code>'.$this->handle.'</code>';
                break;

            case 'view':
                if ($this->id && $count > 0) {
                    return '<a href="'.UrlHelper::cpUrl('sprout/lists/subscribers/'.$this->handle).'" class="go">'.
                        Craft::t('sprout', 'View Subscribers').'</a>';
                }

                return '';
                break;
        }

        return parent::getTableAttributeHtml($attribute);
    }

    /**
     * @return FieldLayout|null
     */
    public function getFieldLayout()
    {
        return Craft::$app->getFields()->getLayoutByType(static::class);
    }

    /**
     * @param bool $isNew
     *
     * @throws Exception
     */
    public function afterSave(bool $isNew)
    {
        // Get the list record
        if (!$isNew) {
            $record = ListsRecord::findOne($this->id);

            if (!$record) {
                throw new ElementNotFoundException('Invalid list ID: '.$this->id);
            }
//            $record->elementId = $this->elementId;
        } else {
            $record = new ListsRecord();
            $record->id = $this->id;

            // Fallback and assign the current listId if no elementId is provided
            $record->elementId = $this->elementId ?? $this->id;
        }

        $record->type = $this->type;
        $record->name = $this->name;
        $record->handle = $this->handle;
        $record->count = $this->count;

        $record->save(false);

        // Update the entry's descendants, who may be using this entry's URI in their own URIs
        Craft::$app->getElements()->updateElementSlugAndUri($this);

        parent::afterSave($isNew);
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['name', 'handle'], 'required'];
        $rules[] = [
            ['handle'],
            SlugValidator::class
        ];
        $rules[] = [
            ['elementId', 'handle'],
            UniqueValidator::class,
            'targetClass' => ListsRecord::class,
            'targetAttribute' => ['elementId', 'handle']
        ];

        return $rules;
    }
}