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
        $this->joinElementTable('sprout_campaignemails');

        $this->query->select([
            'sprout_campaignemails.subjectLine',
            'sprout_campaignemails.campaignTypeId',
            'sprout_campaignemails.recipients',
            'sprout_campaignemails.emailSettings',
            'sprout_campaignemails.defaultBody',
            'sprout_campaignemails.listSettings',
            'sprout_campaignemails.fromName',
            'sprout_campaignemails.fromEmail',
            'sprout_campaignemails.replyToEmail',
            'sprout_campaignemails.enableFileAttachments',
            'sprout_campaignemails.dateScheduled',
            'sprout_campaignemails.dateSent',
            'sprout_campaignemails.dateCreated',
            'sprout_campaignemails.dateUpdated',
        ]);

        if ($this->campaignTypeId) {
            $this->subQuery->andWhere(Db::parseParam(
                'sprout_campaignemails.campaignTypeId', $this->campaignTypeId
            ));
        }

        return parent::beforePrepare();
    }
}