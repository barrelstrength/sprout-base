<?php

namespace barrelstrength\sproutbase\app\import\models;

use craft\base\Model;
use craft\helpers\DateTimeHelper;

class Seed extends Model
{
    /**
     * @var int
     */
    public $itemId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $seedType;

    /**
     * @var string
     */
    public $details = '';

    /**
     * @var array
     */
    public $items;

    /**
     * @var bool
     */
    public $enabled = false;

    /**
     * @var \DateTime
     */
    public $dateCreated;

    /**
     * @var \DateTime
     */
    public $dateUpdated;

    /**
     * Seed constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $currentDate = DateTimeHelper::currentUTCDateTime();
        $this->dateCreated = $currentDate->format('Y-m-d H:i:s');
    }
}