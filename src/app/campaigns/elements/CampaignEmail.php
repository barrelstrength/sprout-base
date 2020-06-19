<?php

namespace barrelstrength\sproutbase\app\campaigns\elements;

use barrelstrength\sproutbase\app\campaigns\elements\db\CampaignEmailQuery;
use barrelstrength\sproutbase\app\campaigns\models\CampaignType;
use barrelstrength\sproutbase\app\campaigns\records\CampaignEmail as CampaignEmailRecord;
use barrelstrength\sproutbase\app\email\base\EmailElement;
use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\mailers\DefaultMailer;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\web\assetbundles\email\EmailAsset;
use Craft;
use craft\base\Element;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\App;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use DateTime;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\ExitException;
use yii\base\InvalidConfigException;

/**
 * Class CampaignEmail
 *
 * @property CampaignType $campaignType
 * @property mixed $emailTemplateId
 * @property string $previewPermission
 * @property DefaultMailer|Mailer $mailer
 */
class CampaignEmail extends EmailElement
{
    // Constants
    // =========================================================================

    const READY = 'ready';
    const DISABLED = 'disabled';
    const PENDING = 'pending';
    const SCHEDULED = 'scheduled';
    const SENT = 'sent';

    /**
     * @var bool
     */
    public $id;

    /**
     * @var bool
     */
    public $campaignTypeId;

    /**
     * @var string
     */
    public $emailSettings;

    /**
     * @var
     */
    public $send;

    /**
     * @var
     */
    public $preview;

    /**
     * @var $dateScheduled DateTime
     */
    public $dateScheduled;

    /**
     * @var $dateSent DateTime
     */
    public $dateSent;

    /**
     * @var
     */
    public $saveAsNew;

    /**
     * The default email message.
     *
     * This field is only visible when no Email Notification Field Layout exists. Once a Field Layout exists, this field will no longer appear in the interface.
     *
     * @var string
     */
    public $defaultBody;

    public $contentCheck;

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Campaign Email');
    }

    /**
     * @return string
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('sprout', 'Campaign Emails');
    }

    /**
     * @return null|string
     */
    public static function refHandle()
    {
        return 'campaignEmail';
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
     * @inheritdoc
     */
    public static function statuses(): array
    {
        return [
            self::DISABLED => Craft::t('sprout', 'Disabled'),
            self::PENDING => Craft::t('sprout', 'Pending'),
            ///self::SCHEDULED => Craft::t('sprout','Scheduled'),
            self::SENT => Craft::t('sprout', 'Sent')
        ];
    }

    /**
     * @return ElementQueryInterface
     */
    public static function find(): ElementQueryInterface
    {
        return new CampaignEmailQuery(static::class);
    }

    /**
     * @param ElementQueryInterface $elementQuery
     * @param array|null $disabledElementIds
     * @param array $viewState
     * @param string|null $sourceKey
     * @param string|null $context
     * @param bool $includeContainer
     * @param bool $showCheckboxes
     *
     * @return string
     * @throws InvalidConfigException
     */
    public static function indexHtml(ElementQueryInterface $elementQuery, /** @noinspection PhpOptionalBeforeRequiredParametersInspection */ array $disabledElementIds = null, array $viewState, /** @noinspection PhpOptionalBeforeRequiredParametersInspection */ string $sourceKey = null, /** @noinspection PhpOptionalBeforeRequiredParametersInspection */ string $context = null, bool $includeContainer, bool $showCheckboxes): string
    {
        $html = parent::indexHtml($elementQuery, $disabledElementIds, $viewState, $sourceKey, $context, $includeContainer,
            $showCheckboxes);

        Craft::$app->getView()->registerAssetBundle(EmailAsset::class);
        Craft::$app->getView()->registerJs('new SproutModal();');
        SproutBase::$app->mailers->includeMailerModalResources();

        return $html;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('sprout', 'All campaigns'),
                'defaultSort' => ['dateCreated', 'desc']
            ]
        ];

        $campaignTypes = SproutBase::$app->campaignTypes->getCampaignTypes();

        $sources[] = ['heading' => Craft::t('sprout', 'Campaigns')];

        foreach ($campaignTypes as $campaignType) {
            $source = [
                'key' => 'campaignTypeId:'.$campaignType->id,
                'label' => Craft::t('sprout', $campaignType->name),
                'criteria' => [
                    'campaignTypeId' => $campaignType->id
                ],
                'defaultSort' => ['dateCreated', 'desc']
            ];

            $sources[] = $source;
        }

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        // Delete
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('sprout', 'Are you sure you want to delete the selected campaign emails?'),
            'successMessage' => Craft::t('sprout', 'Campaign emails deleted.'),
        ]);

        return $actions;
    }


    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'subjectLine' => ['label' => Craft::t('sprout', 'Subject')],
            'contentCheck' => ['label' => Craft::t('sprout', 'Content')],
            'recipientsCheck' => ['label' => Craft::t('sprout', 'Recipients')],
            'dateCreated' => ['label' => Craft::t('sprout', 'Date Created')],
            'dateSent' => ['label' => Craft::t('sprout', 'Date Sent')],
            'send' => ['label' => Craft::t('sprout', 'Send')],
            'preview' => ['label' => Craft::t('sprout', 'Preview'), 'icon' => 'view'],
            'link' => ['label' => Craft::t('sprout', 'Link'), 'icon' => 'world']
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'title' => Craft::t('sprout', 'Title'),
            'sproutemail_campaignemails.dateCreated' => Craft::t('sprout', 'Date Created'),
            'sproutemail_campaignemails.dateUpdated' => Craft::t('sprout', 'Date Updated'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl(
            'sprout/campaigns/edit/'.$this->id
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        $campaignTypeId = $this->campaignTypeId;

        $campaignType = SproutBase::$app->campaignTypes->getCampaignTypeById($campaignTypeId);

        $this->fieldLayoutId = $campaignType->fieldLayoutId;

        return parent::beforeSave($isNew);
    }

    /**
     * @param bool $isNew
     *
     * @throws Exception
     */
    public function afterSave(bool $isNew)
    {
        // Get the entry record
        if (!$isNew) {
            $record = CampaignEmailRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid campaign email ID: '.$this->id);
            }
        } else {
            $record = new CampaignEmailRecord();
            $record->id = $this->id;
        }

        $record->subjectLine = $this->subjectLine;
        $record->defaultBody = $this->defaultBody;
        $record->campaignTypeId = $this->campaignTypeId;
        $record->recipients = $this->recipients;
        $record->emailSettings = $this->emailSettings;
        $record->listSettings = $this->listSettings;
        $record->fromName = $this->fromName;
        $record->fromEmail = $this->fromEmail;
        $record->replyToEmail = $this->replyToEmail;
        $record->enableFileAttachments = $this->enableFileAttachments;
        $record->dateScheduled = $this->dateScheduled;
        $record->dateSent = $this->dateSent;

        $record->save(false);

        // Update the entry's descendants, who may be using this entry's URI in their own URIs
        Craft::$app->getElements()->updateElementSlugAndUri($this);

        parent::afterSave($isNew);
    }

    /**
     * @param string $attribute
     *
     * @return string
     * @throws Exception
     * @throws InvalidConfigException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getTableAttributeHtml(string $attribute): string
    {

        $campaignType = SproutBase::$app->campaignTypes->getCampaignTypeById($this->campaignTypeId);

        $passHtml = '<span class="success" title="'.Craft::t('sprout', 'Passed').'" data-icon="check"></span>';
        $failHtml = '<span class="error" title="'.Craft::t('sprout', 'Failed').'" data-icon="error"></span>';

        if ($attribute === 'send') {
            $mailer = $campaignType->getMailer();

            return Craft::$app->getView()->renderTemplate('sprout/campaigns/_components/elementindex/campaignemail/prepare-link', [
                'campaignEmail' => $this,
                'campaignType' => $campaignType,
                'mailer' => $mailer
            ]);
        }

        if ($attribute === 'preview') {
            return Craft::$app->getView()->renderTemplate('sprout/campaigns/_components/elementindex/campaignemail/preview-links', [
                'email' => $this,
                'campaignType' => $campaignType,
                'type' => 'html'
            ]);
        }

        if ($attribute === 'template') {
            return '<code>'.$campaignType->template.'</code>';
        }

        if ($attribute === 'contentCheck') {
            return $this->isContentReady() ? $passHtml : $failHtml;
        }

        if ($attribute === 'recipientsCheck') {
            return $this->isListReady() ? $passHtml : $failHtml;
        }

        $formatter = Craft::$app->getFormatter();

        if ($attribute === 'dateScheduled') {
            return '<span title="'.$formatter->asDatetime($this->dateScheduled, 'l, d F Y, h:ia').'">'.
                $formatter->asDatetime($this->dateCreated, 'l, d F Y, h:ia').'</span>';
        }

        if ($attribute === 'dateSent' && $this->dateSent) {
            return '<span title="'.$formatter->asDatetime($this->dateSent, 'l, d F Y, h:ia').'">'.
                $formatter->asDatetime($this->dateSent, 'l, d F Y, h:ia').'</span>';
        }

        return parent::getTableAttributeHtml($attribute);
    }

    /**
     * @return bool
     */
    public function isContentReady(): bool
    {
        $campaignType = $this->getCampaignType();

        // todo: update recipient info to be dynamic
        $params = [
            'email' => $this,
            'campaignType' => $campaignType,
            'recipient' => [
                'firstName' => 'First',
                'lastName' => 'Last',
                'email' => 'user@domain.com'
            ]
        ];

        $this->setEventObject($params);

        try {
            $htmlBody = $this->getEmailTemplates()->getHtmlBody();

            return !($htmlBody == null);
        } catch (\Exception $e) {

            return false;
        }
    }

    /**
     * Ensures that $attribute is a valid email address or a placeholder to be parsed later
     *
     * @param $attribute
     */
    public function validateEmailWithOptionalPlaceholder($attribute)
    {
        $value = $this->{$attribute};
        // Validate only if it is not a placeholder and it is not empty
        if (strpos($value, '{') !== 0 &&
            !empty($this->{$attribute}) &&
            !filter_var($value, FILTER_VALIDATE_EMAIL)) {

            $this->addError($attribute, Craft::t('sprout', '{attribute} is not a valid email address.', [
                'attribute' => ($attribute == 'replyToEmail') ? Craft::t('sprout', 'Reply To') : Craft::t('sprout', 'From Email'),
            ]));
        }
    }

    /**
     * Ensures that all email addresses in recipients are valid
     *
     * @param $attribute
     */
    public function validateOnTheFlyRecipients($attribute)
    {
        $value = $this->{$attribute};

        if (is_array($value) && count($value)) {
            foreach ($value as $recipient) {
                if (strpos($recipient, '{') !== 0 &&
                    !empty($this->{$attribute}) &&
                    !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {

                    $this->addError($attribute, Craft::t('sprout', 'All recipients must be placeholders or valid email addresses.', [
                        'attribute' => $attribute,
                    ]));
                }
            }
        }
    }

    /**
     * Determine if this Campaign Email has lists that it will be sent to
     *
     * @return bool
     * @throws Exception
     */
    public function isListReady(): bool
    {
        /**
         * @var $mailer Mailer
         */
        $mailer = $this->getMailer();

        if ($mailer and $mailer->hasLists()) {

            $listSettings = Json::decode($this->listSettings);

            if (is_array($listSettings['listIds']) && count($listSettings['listIds']) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Mailer|DefaultMailer
     * @throws Exception
     */
    public function getMailer(): Mailer
    {
        $campaignType = SproutBase::$app->campaignTypes->getCampaignTypeById($this->campaignTypeId);

        return $campaignType->getMailer();
    }

    /**
     * @return CampaignType
     */
    public function getCampaignType(): CampaignType
    {
        return SproutBase::$app->campaignTypes->getCampaignTypeById($this->campaignTypeId);
    }

    /**
     * @return null|string
     */
    public function getStatus()
    {
        $status = parent::getStatus();

        if ($status == Element::STATUS_ENABLED) {
            $currentTime = DateTimeHelper::currentTimeStamp();
            $dateScheduled = $this->dateScheduled !== null ? $this->dateScheduled->getTimestamp() : null;

            if ($this->dateSent != null) {
                return static::SENT;
            }

            if ($this->dateScheduled != null && $dateScheduled > $currentTime && $this->dateSent == null) {
                return static::SCHEDULED;
            }

            return static::PENDING;
        }

        return $status;
    }

    /**
     * @return null|string
     */
    public function getUriFormat()
    {
        $campaignType = SproutBase::$app->campaignTypes->getCampaignTypeById($this->campaignTypeId);

        if ($campaignType && $campaignType->hasUrls) {
            return $campaignType->urlFormat;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function datetimeAttributes(): array
    {
        $names = parent::datetimeAttributes();
        $names[] = 'dateScheduled';
        $names[] = 'dateSent';

        return $names;
    }

    /**
     * Determine if this Campaign Email is ready to be sent
     *
     * @return bool
     * @throws Exception
     */
    public function isReadyToSend(): bool
    {
        return ($this->getMailer() && $this->isContentReady() && $this->isListReady());
    }

    /**
     * Determine if this Campaign Email is ready to be sent
     *
     * @return bool
     * @throws Exception
     */
    public function isReadyToTest(): bool
    {
        return ($this->getMailer() && $this->isContentReady());
    }

    /**
     * @inheritdoc
     */
    public function getEmailTemplateId(): string
    {
        return $this->getCampaignType()->emailTemplateId;
    }

    public function defaultFromName()
    {
        return App::mailSettings()->fromName;
    }

    public function defaultFromEmail()
    {
        return App::mailSettings()->fromEmail;
    }

    public function defaultReplyToEmail(): string
    {
        return '';
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['fromName', 'fromEmail', 'replyToEmail'], 'required'];
        $rules[] = ['replyToEmail', 'validateEmailWithOptionalPlaceholder'];
        $rules[] = ['fromEmail', 'validateEmailWithOptionalPlaceholder'];
        $rules[] = ['recipients', 'validateOnTheFlyRecipients'];

        return $rules;
    }

    /**
     * @return bool|mixed|null
     * @throws Exception
     * @throws ExitException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function route()
    {
        $campaignType = SproutBase::$app->campaignTypes->getCampaignTypeById($this->campaignTypeId);

        if (!$campaignType) {
            return false;
        }
        $emailTemplates = $this->getEmailTemplates();
        $html = $emailTemplates->getHtmlBody();
        if ($type = Craft::$app->getRequest()->getParam('type')) {
            $html = $emailTemplates->getTextBody();
        }

//        return [
//            'templates/render', [
//                'template' => $sectionSiteSettings[$siteId]->template,
//                'variables' => [
//                    'entry' => $this,
//                ]
//            ]
//        ];

        // Output it into a buffer, in case TasksService wants to close the connection prematurely
        ob_start();

        echo $html;

        // End the request
        Craft::$app->end();

        return null;
    }

    public function getPreviewPermission(): string
    {
        return 'sprout:campaigns:editCampaigns';
    }
}