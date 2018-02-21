<?php

namespace barrelstrength\sproutbase\elements\sproutemail;

use barrelstrength\sproutbase\mailers\DefaultMailer;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutemail\web\assets\email\EmailAsset;
use barrelstrength\sproutemail\elements\actions\DeleteEmail;
use barrelstrength\sproutbase\elements\sproutemail\db\NotificationEmailQuery;
use barrelstrength\sproutbase\records\sproutemail\NotificationEmail as NotificationEmailRecord;
use barrelstrength\sproutemail\SproutEmail;
use craft\base\Element;
use Craft;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use nystudio107\recipe\helpers\Json;
use yii\helpers\ArrayHelper;

class NotificationEmail extends Element
{
    public $subjectLine;
    public $name;
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

    const ENABLED = 'enabled';
    const PENDING = 'pending';
    const DISABLED = 'disabled';

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-base','Notification Email');
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
     * @return array
     */
    public function getStatuses()
    {
        return [
            self::ENABLED => Craft::t('sprout-email', 'enabled'),
            self::PENDING => Craft::t('sprout-email', 'pending'),
            self::DISABLED => Craft::t('sprout-email', 'disabled')
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl(
            'sprout-email/notifications/edit/'.$this->id
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
                'label' => Craft::t('sprout-base','All notifications')
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
            'title' => ['label' => Craft::t('sprout-email', 'Subject Line')],
            'name' => ['label' => Craft::t('sprout-email', 'Notification Name')],
            'dateCreated' => ['label' => Craft::t('sprout-email', 'Date Created')],
            'dateUpdated' => ['label' => Craft::t('sprout-email', 'Date Updated')],
            'send' => ['label' => Craft::t('sprout-email', 'Send')],
            'preview' => ['label' => Craft::t('sprout-email', 'Preview'), 'icon' => 'view']
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'title' => Craft::t('sprout-base','Subject Line'),
            'elements.dateCreated' => Craft::t('sprout-base','Date Created'),
            'elements.dateUpdated' => Craft::t('sprout-base','Date Updated'),
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
            return Craft::$app->getView()->renderTemplate('sprout-email/_partials/notifications/prepare-link', [
                'notification' => $this
            ]);
        }

        if ($attribute === 'preview') {
            $shareUrl = null;

            if ($this->id && $this->getUrl()) {
                $shareUrl = UrlHelper::actionUrl('sprout-email/notification-emails/share-notificationEmail', [
                    'notificationId' => $this->id,
                ]);
            }

            return Craft::$app->getView()->renderTemplate('sprout-email/_partials/notifications/preview-links', [
                'email' => $this,
                'shareUrl' => $shareUrl,
                'type' => $attribute
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
     * @return \craft\models\FieldLayout|null
     */
    public function getFieldLayout()
    {
        return Craft::$app->getFields()->getLayoutByType(static::class);
    }

    /**
     * @param bool $isNew
     *
     * @throws \InvalidArgumentException
     */
    public function afterSave(bool $isNew)
    {
        // Get the entry record
        if (!$isNew) {
            $record = NotificationEmailRecord::findOne($this->id);

            if (!$record) {
                throw new \InvalidArgumentException('Invalid campaign email ID: '.$this->id);
            }
        } else {
            $record = new NotificationEmailRecord();
            $record->id = $this->id;
        }

        $record->name = $this->name;
        $record->template = $this->template;
        $record->eventId = $this->eventId;
        $record->options = $this->options;
        $record->subjectLine = $this->subjectLine;
        $record->fieldLayoutId = $this->fieldLayoutId;
        $record->fromName = $this->fromName;
        $record->fromEmail = $this->fromEmail;
        $record->replyToEmail = $this->replyToEmail;
        $record->recipients = $this->recipients;
        $record->listSettings = $this->listSettings;
        $record->dateCreated = $this->dateCreated;
        $record->dateUpdated = $this->dateUpdated;

        $record->save(false);

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

        Craft::$app->getView()->registerAssetBundle(EmailAsset::class);
        Craft::$app->getView()->registerJs('var sproutModalInstance = new SproutModal(); sproutModalInstance.init();');
        SproutEmail::$app->mailers->includeMailerModalResources();

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

        $actions[] = DeleteEmail::class;

        return $actions;
    }

    /**
     * @return null|string
     */
    public function getUriFormat()
    {
        return 'sprout-email/{slug}';
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
     * @throws \HttpException
     */
    public function route()
    {
        // Only expose notification emails that have tokens and allow Live Preview requests
        if (!Craft::$app->request->getParam(Craft::$app->config->getGeneral()->tokenParam)
            && !Craft::$app->getRequest()->getIsLivePreview()) {
            throw new \HttpException(404);
        }
        $extension = null;

        if ($type = Craft::$app->request->get('type')) {
            $extension = in_array(strtolower($type), ['txt', 'text']) ? '.txt' : null;
        }

        if (!Craft::$app->getView()->doesTemplateExist($this->template.$extension)) {
            $templateName = $this->template.$extension;

            SproutEmail::$app->utilities->addError(Craft::t('sprout-email', "The template '{templateName}' could not be found", [
                'templateName' => $templateName
            ]));
        }

        $event = SproutEmail::$app->notificationEmails->getEventById($this->eventId);

        $object = $event ? $event->getMockedParams() : null;

        return [
            'templates/render', [
                'template' => $this->template.$extension,
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

        $rules[] = [['subjectLine', 'name'], 'required'];

        return $rules;
    }
}