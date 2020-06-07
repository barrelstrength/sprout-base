<?php

namespace barrelstrength\sproutbase\app\reports\services;

use barrelstrength\sproutbase\app\reports\models\ReportGroup;
use barrelstrength\sproutbase\app\reports\models\ReportGroup as ReportGroupModel;
use barrelstrength\sproutbase\app\reports\records\ReportGroup as ReportGroupRecord;
use craft\db\Query;
use Exception;
use InvalidArgumentException;
use Throwable;
use yii\base\Component;
use yii\db\StaleObjectException;
use yii\web\NotFoundHttpException;

/**
 *
 * @property array $reportGroups
 */
class ReportGroups extends Component
{
    /**
     * @param ReportGroupModel $group
     *
     * @return bool
     */
    public function saveGroup(ReportGroupModel $group): bool
    {
        $groupRecord = $this->getGroupRecord($group);

        if (!$groupRecord) {
            throw new InvalidArgumentException('No report group found.');
        }

        $groupRecord->name = $group->name;

        if ($groupRecord->validate()) {
            $groupRecord->save(false);

            // Now that we have an ID, save it on the model & models
            if (!$group->id) {
                $group->id = $groupRecord->id;
            }

            return true;
        }

        $group->addErrors($groupRecord->getErrors());

        return false;
    }

    /**
     * @param $name
     *
     * @return ReportGroupModel|bool
     * @throws Exception
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
     * @return array
     */
    public function getReportGroups(): array
    {
        $query = (new Query())
            ->select('*')
            ->from(ReportGroupRecord::tableName())
            ->indexBy('id');

        $results = $query->all();

        $reportGroups = [];
        foreach ($results as $reportGroup) {
            $reportGroupModel = new ReportGroup();
            $reportGroupModel->id = $reportGroup['id'];
            $reportGroupModel->name = $reportGroup['name'];
            $reportGroups[$reportGroup['id']] = $reportGroupModel;
        }

        return $reportGroups;
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws Exception
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function deleteGroup($id): bool
    {
        $reportGroupRecord = ReportGroupRecord::findOne($id);

        if (!$reportGroupRecord) {
            throw new NotFoundHttpException('Report Group not found.');
        }

        return (bool)$reportGroupRecord->delete();
    }

    /**
     * @param ReportGroupModel $group
     *
     * @return ReportGroupRecord|null
     */
    private function getGroupRecord(ReportGroupModel $group)
    {
        if ($group->id) {
            $groupRecord = ReportGroupRecord::findOne($group->id);

            if (!$groupRecord) {
                throw new InvalidArgumentException('No field group exists with the ID: '.$group->id);
            }
        } else {
            $groupRecord = new ReportGroupRecord();
        }

        return $groupRecord;
    }
}
