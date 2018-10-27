<?php

namespace barrelstrength\sproutbase\app\email\elements;

use barrelstrength\sproutbase\app\email\base\EmailElement;
use barrelstrength\sproutbase\app\email\elements\actions\DeleteNotification;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\app\email\web\assets\base\NotificationAsset;
use barrelstrength\sproutbase\app\email\elements\db\NotificationEmailQuery;
use barrelstrength\sproutbase\app\email\records\NotificationEmail as NotificationEmailRecord;
use Craft;
use craft\behaviors\FieldLayoutBehavior;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use yii\base\Exception;

/**
 * Class NotificationEmail
 *
 * @mixin FieldLayoutBehavior
 */
class NotificationEmail extends EmailElement
{
    /**
     * The Email Notification Field Layout ID
     *
     * @var int
     */
    public $fieldLayoutId;

    /**
     * The Title Format of your Title.
     *
     * @var string
     */
    public $titleFormat;

    /**
     * The handle of the plugin where a notification event exists
     *
     * @var string
     */
    public $pluginHandle;

    /**
     * The Email Template integration handle or folder path of the email templates that should be used when rendering this Notification Email.
     *
     * @var int
     */
    public $emailTemplateId;

    /**
     * The qualified namespace of the Email Notification Event
     *
     * @var string
     */
    public $eventId;

    /**
     * Any options that have been set for your Event. Stored as JSON.
     *
     * @var string
     */
    public $settings;

    /**
     * The default email message.
     *
     * This field is only visible when no Email Notification Field Layout exists. Once a Field Layout exists, this field will no longer appear in the interface.
     *
     * @var string
     */
    public $defaultBody;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-base', 'Notification Email');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'notificationEmail';
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasUris(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getStatuses()
    {
        return [
            self::ENABLED => Craft::t('sprout-base', 'enabled'),
            self::PENDING => Craft::t('sprout-base', 'pending'),
            self::DISABLED => Craft::t('sprout-base', 'disabled')
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        $pluginHandle = Craft::$app->request->getBodyParam('criteria.base') ?: 'sprout-email';

        return UrlHelper::cpUrl(
            $pluginHandle.'/notifications/edit/'.$this->id
        );
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('sprout-base', 'All notifications')
            ]
        ];

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'title' => ['label' => Craft::t('sprout-base', 'Title')],
            'subjectLine' => ['label' => Craft::t('sprout-base', 'Subject Line')],
            'dateCreated' => ['label' => Craft::t('sprout-base', 'Date Created')],
            'send' => ['label' => Craft::t('sprout-base', 'Send')],
            'preview' => ['label' => Craft::t('sprout-base', 'Preview'), 'icon' => 'view']
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'title' => Craft::t('sprout-base', 'Title'),
            'subjectLine' => Craft::t('sprout-base', 'Subject Line'),
            'elements.dateCreated' => Craft::t('sprout-base', 'Date Created'),
            'elements.dateUpdated' => Craft::t('sprout-base', 'Date Updated'),
        ];
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     * @throws \Twig_Error_Loader
     */
    public function getTableAttributeHtml(string $attribute): string
    {
        if ($attribute === 'send') {
            return Craft::$app->getView()->renderTemplate('sprout-base-email/_components/elementindex/NotificationEmail/prepare-link', [
                'notification' => $this
            ]);
        }

        if ($attribute === 'preview') {
            $shareUrl = null;

            if ($this->id && $this->getUrl()) {
                $shareUrl = UrlHelper::actionUrl('sprout/notifications/share-notification-email', [
                    'notificationId' => $this->id,
                ]);
            }
            $pluginHandle = Craft::$app->request->getBodyParam('criteria.base') ?: 'sprout-email';

            return Craft::$app->getView()->renderTemplate('sprout-base-email/_components/elementindex/NotificationEmail/preview-links', [
                'email' => $this,
                'pluginHandle' => $pluginHandle,
                'shareUrl' => $shareUrl,
                'type' => $attribute
            ]);
        }
        return parent::getTableAttributeHtml($attribute);
    }

    /**
     * @inheritdoc
     */
    public static function find(): ElementQueryInterface
    {
        return new NotificationEmailQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout()
    {
        $behaviors = $this->getBehaviors();

        /**
         * @var FieldLayoutBehavior $fieldLayout
         */
        $fieldLayout = $behaviors['fieldLayout'];

        return $fieldLayout->getFieldLayout();
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew)
    {
        /**
         * @var $notificationEmailRecord NotificationEmail
         */
        $notificationEmailRecord = null;

        // Get the entry record
        if (!$isNew) {
            $notificationEmailRecord = NotificationEmailRecord::findOne($this->id);

            if (!$notificationEmailRecord) {
                throw new \InvalidArgumentException('Invalid campaign email ID: '.$this->id);
            }
        } else {
            $notificationEmailRecord = new NotificationEmailRecord();
            $notificationEmailRecord->id = $this->id;
        }

        $notificationEmailRecord->pluginHandle = $this->pluginHandle;
        $notificationEmailRecord->titleFormat = $this->titleFormat;
        $notificationEmailRecord->emailTemplateId = $this->emailTemplateId;
        $notificationEmailRecord->eventId = $this->eventId;
        $notificationEmailRecord->settings = $this->settings;
        $notificationEmailRecord->subjectLine = $this->subjectLine;
        $notificationEmailRecord->defaultBody = $this->defaultBody;
        $notificationEmailRecord->fieldLayoutId = $this->fieldLayoutId;
        $notificationEmailRecord->fromName = $this->fromName;
        $notificationEmailRecord->fromEmail = $this->fromEmail;
        $notificationEmailRecord->replyToEmail = $this->replyToEmail;
        $notificationEmailRecord->singleEmail = $this->singleEmail;
        $notificationEmailRecord->enableFileAttachments = $this->enableFileAttachments;
        $notificationEmailRecord->recipients = $this->recipients;
        $notificationEmailRecord->cc = $this->cc;
        $notificationEmailRecord->bcc = $this->bcc;
        $notificationEmailRecord->listSettings = $this->listSettings;
        $notificationEmailRecord->dateCreated = $this->dateCreated;
        $notificationEmailRecord->dateUpdated = $this->dateUpdated;

        $notificationEmailRecord->save(false);

        // Update the entry's descendants, who may be using this entry's URI in their own URIs
        Craft::$app->getElements()->updateElementSlugAndUri($this, true, true);

        parent::afterSave($isNew);
    }

    /**
     * @@inheritdoc
     *
     * @throws \yii\base\InvalidConfigException
     */
    public static function indexHtml(ElementQueryInterface $elementQuery, array $disabledElementIds = null, array $viewState, string $sourceKey = null, string $context = null, bool $includeContainer, bool $showCheckboxes): string
    {
        $html = parent::indexHtml($elementQuery, $disabledElementIds, $viewState, $sourceKey, $context, $includeContainer,
            true);

        Craft::$app->getView()->registerAssetBundle(NotificationAsset::class);
        Craft::$app->getView()->registerJs('var sproutModalInstance = new SproutModal(); sproutModalInstance.init();');
        SproutBase::$app->mailers->includeMailerModalResources();

        return $html;
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        $actions[] = DeleteNotification::class;

        return $actions;
    }

    /**
     * @@inheritdoc
     *
     * @throws Exception
     */
    public function getUriFormat()
    {
        $pluginHandle = Craft::$app->request->getSegment(1);

        if ($pluginHandle == null) {
            throw new Exception('Invalid integration. No pluginHandle specified');
        }

        return $pluginHandle.'/{slug}';
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function getUrl()
    {
        if ($this->uri !== null) {
            return UrlHelper::siteUrl($this->uri, null, null);
        }

        return null;
    }

    /**
     * @inheritdoc
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['subjectLine', 'fromName', 'fromEmail'], 'required'];
        $rules[] = [['fromName', 'fromEmail', 'replyToEmail'], 'default', 'value' => ''];
        $rules[] = [['fromEmail'], 'email'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'fieldLayout' => [
                'class' => FieldLayoutBehavior::class,
                'elementType' => self::class
            ],
        ]);
    }

    /**
     * Returns a json-decoded array of options
     *
     * @return mixed
     */
    public function getOptions()
    {
        return json_decode($this->settings, true);
    }

    /**
     * @inheritdoc
     */
    public function getEmailTemplateId()
    {
        return $this->emailTemplateId;
    }

    public function isReady()
    {
        return (bool)($this->getStatus() == static::ENABLED);
    }
}