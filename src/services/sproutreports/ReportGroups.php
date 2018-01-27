<?php

namespace barrelstrength\sproutbase\services\sproutreports;

use yii\base\Component;
use barrelstrength\sproutbase\models\sproutreports\ReportGroup as ReportGroupModel;
use barrelstrength\sproutbase\records\sproutreports\ReportGroup as ReportGroupRecord;
use barrelstrength\sproutreports\SproutReports;

/**
 * Class ReportGroups
 *
 * @package barrelstrength\sproutreports\services
 */
class ReportGroups extends Component
{
    /**
     * @param ReportGroupModel $group
     *
     * @return bool
     * @throws \Exception
     */
    public function saveGroup(ReportGroupModel &$group)
    {
        $groupRecord = $this->_getGroupRecord($group);

        $groupRecord->name = $group->name;

        if ($groupRecord->validate()) {
            $groupRecord->save(false);

            // Now that we have an ID, save it on the model & models
            if (!$group->id) {
                $group->id = $groupRecord->id;
            }

            return true;
        } else {
            $group->addErrors($groupRecord->getErrors());
            return false;
        }
    }

    /**
     * @param $name
     *
     * @return ReportGroupModel|bool
     * @throws \Exception
     */
    public function createGroupByName($name)
    {
        $group = new ReportGroupModel();
        $group->name = $name;

        if ($this->saveGroup($group)) {
            return $group;
        }

        return false;
    }


    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAllReportGroups()
    {
        $groups = ReportGroupRecord::find()->indexBy('id')->all();

        return $groups;
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteGroup($id)
    {
        $record = ReportGroupRecord::findOne($id);

        return (bool)$record->delete();
    }

    /**
     * @param ReportGroupModel $group
     *
     * @return ReportGroupRecord|null|static
     * @throws \Exception
     */
    private function _getGroupRecord(ReportGroupModel $group)
    {
        if ($group->id) {
            $groupRecord = ReportGroupRecord::findOne($group->id);

            if (!$groupRecord) {
                throw new \Exception(Craft::t('sprout-import','No field group exists with the ID “{id}”', ['id' => $group->id]));
            }
        } else {
            $groupRecord = new ReportGroupRecord();
        }

        return $groupRecord;
    }
}
