<?php

namespace barrelstrength\sproutbase\app\reports\base;

use barrelstrength\sproutbase\app\reports\elements\Report;
use craft\base\Plugin;

trait DataSourceTrait
{
    /**
     * Set the base URL dynamically so we can manage different URLs across use cases in different modules
     *
     * @var string
     */
    public $baseUrl;

    /**
     * Allows a user to disable a Data Source from displaying in the New Report dropdown
     *
     * @return bool|mixed
     */
    public $allowNew;

    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var Report()
     */
    protected $report;
}
