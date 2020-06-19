<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\config\base\Settings;
use Craft;
use craft\errors\StructureNotFoundException;
use craft\models\Structure;

/**
 *
 * @property array $settingsNavItem
 */
class RedirectsSettings extends Settings
{
    /**
     * @var string
     */
    public $structureId = '';

    /**
     * @var bool
     */
    public $enable404RedirectLog = false;

    /**
     * @var int
     */
    public $total404Redirects = 250;

    /**
     * @var bool
     */
    public $trackRemoteIp = false;

    /**
     * @var string
     */
    public $redirectMatchStrategy = 'urlWithoutQueryStrings';

    /**
     * @var string
     */
    public $queryStringStrategy = 'removeQueryStrings';

    /**
     * @var string
     */
    public $excludedUrlPatterns;

    /**
     * @var int
     */
    public $cleanupProbability = 1000;

    public function getSettingsNavItem(): array
    {
        return [
            'redirects' => [
                'label' => Craft::t('sprout', 'Redirects'),
                'template' => 'sprout/_settings/redirects'
            ]
        ];
    }

    /**
     * @return void|null
     * @throws StructureNotFoundException
     */
    public function beforeAddDefaultSettings()
    {
        $this->structureId = $this->createStructureId();
    }

    /**
     * @return int|null
     * @throws StructureNotFoundException
     */
    private function createStructureId()
    {
        $maxLevels = 1;
        $structure = new Structure();
        $structure->maxLevels = $maxLevels;
        Craft::$app->structures->saveStructure($structure);

        return $structure->id;
    }
}

