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

    public $options;

    public $dataSourceId;

    public $enabled;

    public $groupId;

    public $dateCreated;

    public $dateUpdated;

    public $results;

    /**
     * @return mixed
     */
    public function getDataSourceId()
    {
        return $this->dataSourceId;
    }

    /**
     * @return \barrelstrength\sproutbase\contracts\sproutreports\BaseDataSource|null
     */
    public function getDataSource()
    {
        $dataSource = SproutBase::$app->dataSources->getDataSourceById($this->dataSourceId);

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
        $optionsArray = Json::decode($this->options);
        $options = $dataSource->prepOptions($optionsArray);

        return Craft::$app->getView()->renderObjectTemplate($this->nameFormat, $options);
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        $options = $this->options;

        if (is_string($this->options)) {
            $options = json_decode($this->options);
        }

        return $options;
    }

    /**
     * Returns a user supplied option if it exists or $default otherwise
     *
     * @param string     $name
     * @param null|mixed $default
     *
     * @return null
     */
    public function getOption($name, $default = null)
    {
        $options = $this->getOptions();

        if (is_string($name) && !empty($name) && isset($options->$name)) {
            return $options->$name;
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
            'description', 'allowHtml', 'options',
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