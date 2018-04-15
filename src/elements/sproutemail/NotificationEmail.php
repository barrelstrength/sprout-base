<?php

namespace barrelstrength\sproutbase\elements\sproutemail;

use barrelstrength\sproutbase\contracts\sproutemail\BaseMailer;
use barrelstrength\sproutbase\elements\sproutemail\actions\DeleteNotification;
use barrelstrength\sproutbase\integrations\emailtemplates\BasicTemplates;
use barrelstrength\sproutbase\mailers\DefaultMailer;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\web\assets\sproutemail\NotificationAsset;
use barrelstrength\sproutbase\elements\sproutemail\db\NotificationEmailQuery;
use barrelstrength\sproutbase\records\sproutemail\NotificationEmail as NotificationEmailRecord;
use craft\base\Element;
use Craft;
use craft\behaviors\FieldLayoutBehavior;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use yii\base\Exception;

class NotificationEmail extends Element
{
    // Email Status Constants
    const ENABLED = 'enabled';
    const PENDING = 'pending';
    const DISABLED = 'disabled';

    /**
     * The Subject Line of your email. Your title will also default to the Subject Line unless you set a Title Format.
     *
     * @var string
     */
    public $subjectLine;

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
    public $pluginId;

    /**
     * The Email Template integration handle or folder path of the email templates that should be used when rendering this Notification Email.
     * @var string
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
    public $options;

    /**
     * A comma, delimited list of recipients
     *
     * @var string
     */
    public $recipients;

    /**
     * List settings.
     *
     * List settings HTML is provided by a List integration. Values will be saved in JSON format and will be processed by the active mailer and list integration.
     *
     * @var string
     */
    public $listSettings;

    /**
     * The sender name
     *
     * @var string
     */
    public $fromName;

    /**
     * The sender email
     *
     * @var string
     */
    public $fromEmail;

    /**
     * The sender replyTo email, if different than the sender email
     *
     * @var string
     */
    public $replyToEmail;

    /**
     * Enable or disable file attachments when notification emails are sent.
     *
     * If disabled, files will still be stored in Craft after form submission. This only determines if they should also be sent via email.
     *
     * @var bool
     */
    public $enableFileAttachments;

    /**
     * The Email Notification Field Layout ID
     *
     * @var int
     */
    public $fieldLayoutId;

    /**
     * The default email message.
     *
     * This field is only visible when no Email Notification Field Layout exists. Once a Field Layout exists, this field will no longer appear in the interface.
     *
     * @var string
     */
    public $defaultBody;

    /**
     * @var \DateTime
     */
    public $dateCreated;

    /**
     * @var \DateTime
     */
    public $dateUpdated;

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
    public static function isLocalized(): bool
    {
        return false;
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
     */
    public function getTableAttributeHtml(string $attribute): string
    {
        if ($attribute === 'send') {
            return Craft::$app->getView()->renderTemplate('sprout-base/sproutemail/notifications/_partials/prepare-link', [
                'notification' => $this
            ]);
        }

        if ($attribute === 'preview') {
            $shareUrl = null;

            if ($this->id && $this->getUrl()) {
                $shareUrl = UrlHelper::actionUrl('sprout-base/notifications/share-notification-email', [
                    'notificationId' => $this->id,
                ]);
            }
            $pluginHandle = Craft::$app->request->getBodyParam('criteria.base') ?: 'sprout-email';

            return Craft::$app->getView()->renderTemplate('sprout-base/sproutemail/notifications/_partials/preview-links', [
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

        $notificationEmailRecord->pluginId = $this->pluginId;
        $notificationEmailRecord->titleFormat = $this->titleFormat;
        $notificationEmailRecord->emailTemplateId = $this->emailTemplateId;
        $notificationEmailRecord->eventId = $this->eventId;
        $notificationEmailRecord->options = $this->options;
        $notificationEmailRecord->subjectLine = $this->subjectLine;
        $notificationEmailRecord->defaultBody = $this->defaultBody;
        $notificationEmailRecord->fieldLayoutId = $this->fieldLayoutId;
        $notificationEmailRecord->fromName = $this->fromName;
        $notificationEmailRecord->fromEmail = $this->fromEmail;
        $notificationEmailRecord->replyToEmail = $this->replyToEmail;
        $notificationEmailRecord->enableFileAttachments = $this->enableFileAttachments;
        $notificationEmailRecord->recipients = $this->recipients;
        $notificationEmailRecord->listSettings = $this->listSettings;
        $notificationEmailRecord->dateCreated = $this->dateCreated;
        $notificationEmailRecord->dateUpdated = $this->dateUpdated;

        $notificationEmailRecord->save(false);

        // Update the entry's descendants, who may be using this entry's URI in their own URIs
        Craft::$app->getElements()->updateElementSlugAndUri($this, true, true);

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getUriFormat()
    {
        $pluginHandle = Craft::$app->request->getSegment(1);

        if ($pluginHandle == null) {
            throw new Exception('Invalid integration. No pluginId specified');
        }

        return $pluginHandle.'/{slug}';
    }

    /**
     * @inheritdoc
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
     */
    public function route()
    {
        //Only expose notification emails that have tokens and allow Live Preview requests
        if (!Craft::$app->request->getParam(Craft::$app->config->getGeneral()->tokenParam)
            && !Craft::$app->getRequest()->getIsLivePreview()) {
            throw new Exception(404);
        }
        $extension = null;

        if ($type = Craft::$app->request->get('type')) {
            $extension = in_array(strtolower($type), ['txt', 'text']) ? '.txt' : null;
        }

        $templateName = $this->template.$extension;

        if (empty($this->template)) {
            $template = SproutBase::$app->sproutEmail->getEmailTemplate();

            $templateName = $template.$extension;
        }

        if (!Craft::$app->getView()->doesTemplateExist($templateName)) {

            SproutBase::$app->common->addError(Craft::t('sprout-base', "The template '{templateName}' could not be found", [
                'templateName' => $templateName
            ]));
        }

        $event = SproutBase::$app->notificationEvents->getEventById($this->eventId);

        $object = $event ? $event->getMockedParams() : null;

        return [
            'templates/render', [
                'template' => $templateName,
                'variables' => [
                    'email' => $this,
                    'object' => $object
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['subjectLine', 'fromName', 'fromEmail'], 'required'];
        $rules[] = [['fromName', 'fromEmail', 'replyToEmail'], 'default', 'value' => ''];
        $rules[] = [['fromEmail', 'replyToEmail'], 'email'];

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
     * All Notification Emails use the Default Mailer.
     *
     * The Email Service provide can be update via Craft's Email Settings
     *
     * @return BaseMailer
     */
    public function getMailer()
    {
        return SproutBase::$app->mailers->getMailerByName(DefaultMailer::class);
    }

    /**
     * Confirm that an email is enabled.
     *
     * @return bool
     */
    public function isReady()
    {
        return (bool)($this->getStatus() == static::ENABLED);
    }

    /**
     * @param mixed|null $element
     *
     * @throws \Exception
     * @return array|string
     */
    public function getRecipients($element = null)
    {
        return SproutBase::$app->mailers->getRecipients($element, $this);
    }
}