<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\redirects\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class RedirectQuery extends ElementQuery
{
    public $oldUrl;

    public $newUrl;

    public $method;

    public $matchStrategy;

    public $count;

    public $status;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->withStructure === null) {
            $this->withStructure = true;
        }

        parent::init();
    }

    /**
     * @param false|int|int[]|null $id
     *
     * @return $this|ElementQuery
     */
    public function id($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('sprout_redirects');

        $this->query->select([
            'sprout_redirects.id',
            'sprout_redirects.oldUrl',
            'sprout_redirects.newUrl',
            'sprout_redirects.method',
            'sprout_redirects.matchStrategy',
            'sprout_redirects.count',
            'sprout_redirects.dateLastUsed',
            'sprout_redirects.lastRemoteIpAddress',
            'sprout_redirects.lastReferrer',
            'sprout_redirects.lastUserAgent'
        ]);

        if ($this->id) {
            $this->subQuery->andWhere(Db::parseParam(
                'sprout_redirects.id', $this->id)
            );
        }

        if ($this->oldUrl) {
            $this->subQuery->andWhere(Db::parseParam(
                'sprout_redirects.oldUrl', $this->oldUrl)
            );
        }

        if ($this->newUrl) {
            $this->subQuery->andWhere(Db::parseParam(
                'sprout_redirects.newUrl', $this->newUrl)
            );
        }

        if ($this->method) {
            $this->subQuery->andWhere(Db::parseParam(
                'sprout_redirects.method', $this->method)
            );
        }

        return parent::beforePrepare();
    }
}
