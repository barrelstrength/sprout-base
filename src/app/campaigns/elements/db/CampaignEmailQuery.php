<?php

namespace barrelstrength\sproutbase\app\campaigns\elements\db;

use barrelstrength\sproutbase\app\campaigns\records\CampaignEmail as CampaignEmailRecord;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class CampaignEmailQuery extends ElementQuery
{
    public $campaignTypeId;

    public $orderBy;

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('sprout_campaign_emails');

        $this->query->select([
            'sprout_campaign_emails.subjectLine',
            'sprout_campaign_emails.campaignTypeId',
            'sprout_campaign_emails.recipients',
            'sprout_campaign_emails.emailSettings',
            'sprout_campaign_emails.defaultBody',
            'sprout_campaign_emails.listSettings',
            'sprout_campaign_emails.fromName',
            'sprout_campaign_emails.fromEmail',
            'sprout_campaign_emails.replyToEmail',
            'sprout_campaign_emails.enableFileAttachments',
            'sprout_campaign_emails.dateScheduled',
            'sprout_campaign_emails.dateSent',
            'sprout_campaign_emails.dateCreated',
            'sprout_campaign_emails.dateUpdated',
        ]);

        if ($this->campaignTypeId) {
            $this->subQuery->andWhere(Db::parseParam(
                'sprout_campaign_emails.campaignTypeId', $this->campaignTypeId
            ));
        }

        return parent::beforePrepare();
    }
}