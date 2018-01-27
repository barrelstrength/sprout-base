<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services\sproutfields;

use barrelstrength\sproutbase\models\sproutfields\Address as AddressModel;
use barrelstrength\sproutbase\events\OnSaveAddressEvent;
use barrelstrength\sproutbase\records\sproutfields\Address as AddressRecord;
use Craft;
use craft\base\Component;

class Address extends Component
{
    const EVENT_ON_SAVE_ADDRESS = 'onSaveAddressEvent';

    /**
     * @param string   $namespace
     * @param int|null $modelId
     *
     * @return bool
     * @throws \Exception
     */
    public function saveAddressByPost($namespace = 'address', int $modelId = null)
    {
        if (Craft::$app->getRequest()->getBodyParam($namespace) != null) {
            $addressInfo = Craft::$app->getRequest()->getBodyParam($namespace);

            if ($modelId != null) {
                $addressInfo['modelId'] = $modelId;
            }

            $addressInfoModel = new AddressModel($addressInfo);

            if ($addressInfoModel->validate() == true && $this->saveAddress($addressInfoModel)) {
                return $addressInfoModel->id;
            }
        }

        return false;
    }

    /**
     * @param AddressModel $model
     * @param string       $source
     *
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function saveAddress(AddressModel $model, $source = '')
    {
        $result = false;

        $record = new AddressRecord;

        if (!empty($model->id)) {
            $record = AddressRecord::findOne($model->id);

            if (!$record) {
                throw new \Exception(Craft::t('sprout-base','No entry exists with the ID “{id}”', ['id' => $model->id]));
            }
        }

        $attributes = $model->getAttributes();

        if (!empty($attributes)) {
            foreach ($model->getAttributes() as $handle => $value) {
                $record->setAttribute($handle, $value);
            }
        }

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

        if ($model->validate()) {
            try {
                if ($record->save()) {
                    if ($transaction) {
                        $transaction->commit();
                    }

                    $model->id = $record->id;

                    $result = true;

                    $event = new OnSaveAddressEvent([
                        'model' => $model,
                        'source' => $source
                    ]);

                    $this->trigger(self::EVENT_ON_SAVE_ADDRESS, $event);
                }
            } catch (\Exception $e) {
                if ($transaction) {
                    $transaction->rollback();
                }

                throw $e;
            }
        }

        if (!$result) {
            if ($transaction) {
                $transaction->rollback();
            }
        }

        return $result;
    }

    /**
     * @param $id
     *
     * @return AddressModel
     */
    public function getAddressById($id)
    {
        if ($record = AddressRecord::findOne($id)) {
            return new AddressModel($record->getAttributes());
        } else {
            return new AddressModel();
        }
    }

    /**
     * @param null $id
     *
     * @return bool|false|int
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteAddressById($id = null)
    {
        $record = AddressRecord::findOne($id);
        $result = false;

        if ($record) {
            $result = $record->delete();
        }

        return $result;
    }

    /**
     * @param null $id
     *
     * @return bool|false|int
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteAddressByModelId($id = null)
    {
        $record = AddressRecord::findOne(['modelId' => $id]);
        $result = false;

        if ($record) {
            $result = $record->delete();
        }

        return $result;
    }
}