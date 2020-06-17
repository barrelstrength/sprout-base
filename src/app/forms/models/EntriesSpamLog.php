<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\models;

use barrelstrength\sproutbase\app\forms\elements\Entry;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Model;

/**
 * @property null|Entry $entry
 */
class EntriesSpamLog extends Model
{
    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var int|null
     */
    public $entryId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $errors;

    /**
     * @var string
     */
    public $dateCreated;

    /**
     * @var string
     */
    public $dateUpdated;

    /**
     * @var string
     */
    public $uid;

    /**
     * Use the translated section name as the string representation.
     *
     * @inheritdoc
     */
    public function __toString()
    {
        return Craft::t('sprout', $this->id);
    }

    /**
     * @return Entry|null
     */
    public function getEntry()
    {
        return SproutBase::$app->formEntries->getEntryById($this->entryId);
    }
}