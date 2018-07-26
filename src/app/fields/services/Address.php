<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\services;

use barrelstrength\sproutbase\app\fields\models\Address as AddressModel;
use barrelstrength\sproutbase\app\fields\events\OnSaveAddressEvent;
use barrelstrength\sproutbase\app\fields\records\Address as AddressRecord;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Component;

class Address extends Component
{
    const EVENT_ON_SAVE_ADDRESS = 'onSaveAddressEvent';

    /**
     * @param string   $namespace
     * @param int|null $fieldId
     *
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function saveAddressByPost($namespace = 'address', int $fieldId = null)
    {
        if (Craft::$app->getRequest()->getBodyParam($namespace) != null) {
            $addressInfo = Craft::$app->getRequest()->getBodyParam($namespace);

            if ($fieldId != null) {
                $addressInfo['fieldId'] = $fieldId;
            }

            $isDelete = $addressInfo['delete'] ?? null;
            $addressId = $addressInfo['id'] ?? null;

            if ($isDelete && $addressId) {
                SproutBase::$app->addressField->deleteAddressById($addressId);

                return false;
            }
            unset($addressInfo['delete']);
            
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
                throw new \InvalidArgumentException(Craft::t('sprout-base', 'No Address exists with the ID “{id}”', ['id' => $model->id]));
            }
        }

        $attributes = $model->getAttributes();
        $record->setAttributes($attributes, false);

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
                    $transaction->rollBack();
                }

                throw $e;
            }
        }

        if (!$result) {
            $transaction->rollBack();
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
        $model = new AddressModel();
        if ($record = AddressRecord::findOne($id)) {
            $model->setAttributes($record->getAttributes(), false);
        }

        return $model;
    }

    public function getAddress($elementId, $siteId, $fieldId)
    {
        $model = new AddressModel();

        if ($record = AddressRecord::findOne([
            'elementId' => $elementId,
            'siteId' => $siteId,
            'fieldId' => $fieldId
        ])) {
            $model->setAttributes($record->getAttributes(), false);
        }

        return $model;
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
    public function deleteAddressByFieldId($id = null)
    {
        $record = AddressRecord::findOne(['fieldId' => $id]);
        $result = false;

        if ($record) {
            $result = $record->delete();
        }

        return $result;
    }
}