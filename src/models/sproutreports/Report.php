<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\models\sproutreports;

use barrelstrength\sproutbase\SproutBase;
use craft\base\Model;
use barrelstrength\sproutbase\records\sproutreports\Report as ReportRecord;
use craft\helpers\Json;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;
use Craft;

class Report extends Model
{
    public $id;

    public $name;

    public $nameFormat;

    public $handle;

    public $description;

    public $allowHtml;

    public $settings;

    public $dataSourceId;

    public $dataSourceSlug;

    public $enabled;

    public $groupId;

    public $dateCreated;

    public $dateUpdated;

    public $results;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->dataSourceId;
    }

    /**
     * @return \barrelstrength\sproutbase\contracts\sproutreports\BaseDataSource|null
     */
    public function getDataSource()
    {
        $dataSource = SproutBase::$app->dataSources->getDataSourceById($this->dataSourceId);

        if ($dataSource === null) {
            return null;
        }

        $dataSource->setReport($this);

        return $dataSource;
    }

    /**
     * @return string
     * @throws \yii\base\Exception
     */
    public function processNameFormat()
    {
        $dataSource = $this->getDataSource();
        $settingsArray = Json::decode($this->settings);
        $settings = $dataSource->prepSettings($settingsArray);

        return Craft::$app->getView()->renderObjectTemplate($this->nameFormat, $settings);
    }

    /**
     * @return mixed
     */
    public function getSettings()
    {
        $settings = $this->settings;

        if (is_string($this->settings)) {
            $settings = json_decode($this->settings, true);
        }

        return $settings;
    }

    /**
     * Returns a user supplied setting if it exists or $default otherwise
     *
     * @param string     $name
     * @param null|mixed $default
     *
     * @return null
     */
    public function getSetting($name, $default = null)
    {
        $settings = $this->getSettings();

        if (isset($settings[$name])) {

            return $settings[$name];
        }

        return $default;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'handle'], 'required'],
            [['handle'], HandleValidator::class, 'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']],
            [['name', 'handle'], UniqueValidator::class, 'targetClass' => ReportRecord::class]
        ];
    }

    /**
     * @return array|string[]
     */
    public function safeAttributes()
    {
        return [
            'id', 'name', 'nameFormat', 'handle',
            'description', 'allowHtml', 'settings',
            'dataSourceId', 'enabled', 'groupId',
            'dateCreated', 'dateUpdated'
        ];
    }

    /**
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getDataSource()->getUrl('edit/'.$this->id);
    }

    /**
     * @param array $results
     */
    public function setResults(array $results = [])
    {
        $this->results = $results;
    }

    /**
     * @param string $message
     */
    public function setResultsError($message)
    {
        $this->addError('results', $message);
    }
}