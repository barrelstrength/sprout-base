<?php

namespace barrelstrength\sproutbase\app\reports\datasources;

use barrelstrength\sproutbase\app\reports\base\DataSource;
use Craft;
use craft\base\MissingComponentInterface;
use craft\base\MissingComponentTrait;

/**
 *
 * @property string $description
 */
class MissingDataSource extends DataSource implements MissingComponentInterface
{
    use MissingComponentTrait;

    /**
     * Dynamically set description
     *
     * @var string
     */
    public $dynamicDescription;

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Missing Data Source');
    }

    /**
     * Set the description dynamically
     *
     * @var string
     */
    public function setDescription(string $message)
    {
        $this->dynamicDescription = $message;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        if ($this->dynamicDescription) {
            $message = $this->dynamicDescription;
        } else {
            $message = Craft::t('sprout', 'Unable to find installed Data Source. See logs for details.');
        }

        return ' <em class="error">Unable to find class '.$message.'</em>';
    }
}
