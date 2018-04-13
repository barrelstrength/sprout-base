<?php

namespace barrelstrength\sproutbase\elements\sproutemail;

use barrelstrength\sproutbase\elements\sproutemail\actions\DeleteNotification;
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

class NotificationEmail extends Element
{
    public $subjectLine;
    public $pluginId;
    public $titleFormat;
    public $template;
    public $eventId;
    public $options;
    public $recipients;
    public $listSettings;
    public $fromName;
    public $fromEmail;
    public $replyToEmail;
    public $enableFileAttachments;
    public $dateCreated;
    public $dateUpdated;
    public $fieldLayoutId;
    public $send;
    public $preview;
    public $defaultBody;

    const ENABLED = 'enabled';
    const PENDING = 'pending';
    const DISABLED = 'disabled';

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-base', 'Notification Email');
    }

    /**
     * @return null|string
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
     * @return array
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
            $pluginHandle . '/notifications/edit/'.$this->id
        );
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
     * @param string $attribute
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
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
                'email'        => $this,
                'pluginHandle' => $pluginHandle,
                'shareUrl'     => $shareUrl,
                'type'         => $attribute
            ]);
        }
        return parent::getTableAttributeHtml($attribute);
    }

    /**
     * @return ElementQueryInterface
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
     * @param bool $isNew
     *
     * @throws \InvalidArgumentException
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
        $notificationEmailRecord->template = $this->template;
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
     * @param ElementQueryInterface $elementQuery
     * @param array|null            $disabledElementIds
     * @param array                 $viewState
     * @param string|null           $sourceKey
     * @param string|null           $context
     * @param bool                  $includeContainer
     * @param bool                  $showCheckboxes
     *
     * @return string
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
     * @return \barrelstrength\sproutbase\contracts\sproutemail\BaseMailer|null
     */
    public function getMailer()
    {
        // All Notification Emails use the Default Mailer
        return SproutBase::$app->mailers->getMailerByName(DefaultMailer::class);
    }

    /**
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
     * @return null|string
     * @throws \Exception
     */
    public function getUriFormat()
    {
        $pluginHandle = Craft::$app->request->getSegment(1);
        
        if ($pluginHandle == null) {
            throw new \Exception('Invalid integration. No pluginId specified');
        }

        return $pluginHandle . '/{slug}';
    }

    /**
     * @return null|string
     * @throws \yii\base\Exception
     */
    public function getUrl()
    {
        if ($this->uri !== null) {
            return UrlHelper::siteUrl($this->uri, null, null);
        }

        return null;
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function route()
    {
         //Only expose notification emails that have tokens and allow Live Preview requests
        if (!Craft::$app->request->getParam(Craft::$app->config->getGeneral()->tokenParam)
            && !Craft::$app->getRequest()->getIsLivePreview()) {
            throw new \Exception(404);
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

        $event = SproutBase::$app->notifications->getEventById($this->eventId);

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
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['subjectLine'], 'required'];
        $rules[] = [['fromName', 'fromEmail', 'replyToEmail'], 'default', 'value' => ''];

        return $rules;
    }

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
     * @return array
     */
    public function getTemplateOptions()
    {
        $templates = SproutBase::$app->template->getAllGlobalTemplates();
        $templateIds = [];
        $options = [
            [
                'label' => \Craft::t('sprout-base', 'Select...'),
                'value' => ''
            ]
        ];

        foreach ($templates as $template) {
            $options[] = [
                'label' => $template->getName(),
                'value' => $template->getTemplateId()
            ];
            $templateIds[] = $template->getTemplateId();
        }

        $templateFolder = '';
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-email');

        if ($plugin){
            $settings = $plugin->getSettings();
            $templateFolder = $settings->templateFolderOverride;
        }

        $options[] = [
            'optgroup' => Craft::t('sprout-base', 'Custom Template Folder')
        ];

        if (!in_array($templateFolder, $templateIds) && $templateFolder != '') {
            $options[] = [
                'label' => $templateFolder,
                'value' => $templateFolder
            ];
        }

        $options[] = [
            'label' => Craft::t('sprout-base', 'Add Custom'),
            'value' => 'custom'
        ];

        return $options;
    }
}