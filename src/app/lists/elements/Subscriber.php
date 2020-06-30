<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\elements;

use barrelstrength\sproutbase\app\lists\base\ListType;
use barrelstrength\sproutbase\app\lists\base\SubscriberInterface;
use barrelstrength\sproutbase\app\lists\elements\actions\DeleteSubscriber;
use barrelstrength\sproutbase\app\lists\elements\db\SubscriberQuery;
use barrelstrength\sproutbase\app\lists\listtypes\SubscriberList;
use barrelstrength\sproutbase\app\lists\records\ListElement as ListElementRecord;
use barrelstrength\sproutbase\app\lists\records\Subscriber as SubscribersRecord;
use barrelstrength\sproutbase\app\lists\records\Subscription as SubscriptionRecord;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Element;
use craft\db\Query;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use craft\validators\UniqueValidator;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Exception;

/**
 *
 * @property array $listIds
 * @property array $lists
 */
class Subscriber extends Element implements SubscriberInterface
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var int
     */
    public $itemId;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var ListType
     */
    public $listType;

    /**
     * @var array
     */
    public $listElements;

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Subscriber');
    }

    /**
     * @return string
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('sprout', 'Subscribers');
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @return SubscriberQuery The newly created [[SubscriberQuery]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new SubscriberQuery(static::class);
    }

    /**
     * @param string|null $context
     *
     * @return array
     * @throws \Exception
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('sprout', 'All Subscribers')
            ]
        ];

        $listType = SproutBase::$app->lists->getListType(SubscriberList::class);

        /** @var ListElement[] $lists */
        $lists = ListElement::find()
            ->where([
                'sproutlists_lists.type' => SubscriberList::class
            ])->all();

        if (!empty($lists)) {
            $sources[] = [
                'heading' => $listType::displayName()
            ];

            foreach ($lists as $list) {
                $source = [
                    'key' => 'lists:'.$list->id,
                    'label' => $list->name,
                    'data' => ['handle' => $list->handle],
                    'criteria' => ['listId' => $list->id]
                ];

                $sources[] = $source;
            }
        }

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'email' => ['label' => Craft::t('sprout', 'Email')],
            'firstName' => ['label' => Craft::t('sprout', 'First Name')],
            'lastName' => ['label' => Craft::t('sprout', 'Last Name')],
            'dateCreated' => ['label' => Craft::t('sprout', 'Date Created')],
            'dateUpdated' => ['label' => Craft::t('sprout', 'Date Updated')]
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        $actions[] = DeleteSubscriber::class;

        return $actions;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->email;
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl(
            'sprout/lists/subscribers/edit/'.$this->id
        );
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return FieldLayout|null
     */
    public function getFieldLayout()
    {
        return Craft::$app->getFields()->getLayoutByType(static::class);
    }

    /**
     * @return null|string
     */
    public function getUriFormat()
    {
        return 'sprout/lists/{slug}';
    }

    /**
     * @return null|string
     * @throws \yii\base\Exception
     */
    public function getUrl()
    {
        if ($this->uri !== null) {
            return UrlHelper::siteUrl($this->uri);
        }

        return null;
    }

    /**
     * Gets an array of SproutLists_ListModels to which this subscriber is subscribed.
     *
     * @return SubscriberQuery|ActiveQuery
     * @throws InvalidConfigException
     */
    public function getLists(): ActiveQuery
    {
        $subscriberRecord = SubscribersRecord::findOne($this->id);

        if ($subscriberRecord) {
            return $subscriberRecord->getLists();
        }

        return new ActiveQuery(__CLASS__);
    }

    /**
     * @param bool $isNew
     *
     * @throws Exception
     */
    public function afterSave(bool $isNew)
    {
        $settings = SproutBase::$app->settings->getSettingsByKey('lists');

        // Get the list record
        if (!$isNew) {
            $subscriberRecord = SubscribersRecord::findOne($this->id);

            if (!$subscriberRecord) {
                throw new Exception('Invalid Subscriber ID: '.$this->id);
            }
        } else {
            $subscriberRecord = new SubscribersRecord();
            $subscriberRecord->id = $this->id;
        }

        $user = null;

        // Sync updates with Craft User if User Sync enabled
        if ($this->email && $settings->enableUserSync) {

            // Get an existing matched User by Element ID if we have them
            if ($this->userId) {
                $user = Craft::$app->users->getUserById($this->userId);
            }

            if ($user === null) {
                // If we don't have a match, see if we can find a matching user by email
                $user = Craft::$app->users->getUserByUsernameOrEmail($this->email);

                // Set to null when updating a matched email
                $this->userId = null;
            }

            if ($user !== null) {
                $this->userId = $user->id;
            }
        }

        // Prepare our Subscriber Record
        $subscriberRecord->userId = $this->userId;
        $subscriberRecord->email = $this->email;
        $subscriberRecord->firstName = $this->firstName;
        $subscriberRecord->lastName = $this->lastName;

        $result = $subscriberRecord->save(false);

        if ($result &&
            $isNew === false &&
            $user !== null &&
            $settings->enableUserSync) {
            // Sync updates with existing Craft User if User Sync enabled
            Craft::$app->getDb()->createCommand()->update(
                '{{%users}}',
                [
                    'email' => $subscriberRecord->email ?? $user->email,
                    'firstName' => $subscriberRecord->firstName ?? $user->firstName,
                    'lastName' => $subscriberRecord->lastName ?? $user->lastName
                ],
                ['id' => $subscriberRecord->userId],
                [],
                false
            )
                ->execute();
        }

        // @todo - Temporary: We should support updating multiple lists at once on the front-end as well
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $itemIds = (new Query())
                ->select('itemId')
                ->from(SubscriptionRecord::tableName().' subscription')
                ->leftJoin(ListElementRecord::tableName().' list', '[[subscription.listId]] = [[list.id]]')
                ->where([
                    'list.type' => SubscriberList::class,
                    'subscription.itemId' => $this->id
                ])
                ->distinct()
                ->column();

            SubscriptionRecord::deleteAll(['[[itemId]]' => $itemIds]);

            if ($this->listElements) {
                foreach ($this->listElements as $listId) {
                    $subscriptionRecord = new SubscriptionRecord();
                    $subscriptionRecord->listId = $listId;
                    $subscriptionRecord->itemId = $this->id;

                    if (!$subscriptionRecord->save(false)) {
                        throw new Exception(Craft::t('sprout', 'Unable to save subscription while saving subscriber.'));
                    }
                }
            }
        }

        parent::afterSave($isNew);
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['email'], 'required'];
        $rules[] = [['email'], 'email'];
        $rules[] = [
            ['email'],
            UniqueValidator::class,
            'targetClass' => SubscribersRecord::class
        ];

        return $rules;
    }
}