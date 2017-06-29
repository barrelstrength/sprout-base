<?php
namespace barrelstrength\sproutcore\services\sproutfields;

use barrelstrength\sproutcore\SproutCore;
use Craft;
use craft\base\Component;

class Address extends Component
{
	public function saveAddressByPost($namespace = 'address', int $modelId = null)
	{
		if (Craft::$app->getRequest()->getBodyParam($namespace) != null)
		{
			$addressInfo = Craft::$app->getRequest()->getBodyParam($namespace);

			if ($modelId != null)
			{
				$addressInfo['modelId'] = $modelId;
			}

			$addressInfoModel = SproutSeo_AddressModel::populateModel($addressInfo);

			if ($addressInfoModel->validate() == true && $this->saveAddress($addressInfoModel))
			{
				return $addressInfoModel->id;
			}
		}

		return false;
	}

	public function saveAddress(SproutSeo_AddressModel $model, $source = '')
	{
		$result = false;

		$record = new SproutSeo_AddressRecord;

		if (!empty($model->id))
		{
			$record = SproutSeo_AddressRecord::model()->findById($model->id);

			if (!$record)
			{
				throw new Exception(SproutSeo::t('No entry exists with the ID “{id}”', ['id' => $model->id]));
			}
		}

		$attributes = $model->getAttributes();

		if (!empty($attributes))
		{
			foreach ($model->getAttributes() as $handle => $value)
			{
				$record->setAttribute($handle, $value);
			}
		}

		$transaction = Craft::$app->db->getCurrentTransaction() === null ? Craft::$app->db->beginTransaction() : null;

		if ($record->validate())
		{
			try
			{
				if ($record->save())
				{
					if ($transaction && $transaction->active)
					{
						$transaction->commit();
					}

					$model->setAttributes($record->getAttributes());

					$result = true;

					$eventParams = [
						'model'  => $model,
						'source' => $source
					];

					$event = new Event($this, $eventParams);

					SproutSeo::$app->onSaveAddressInfo($event);
				}
			}
			catch (\Exception $e)
			{
				if ($transaction && $transaction->active)
				{
					$transaction->rollback();
				}

				throw $e;
			}
		}
		else
		{
			$model->addErrors($record->getErrors());
		}

		if (!$result)
		{
			if ($transaction && $transaction->active)
			{
				$transaction->rollback();
			}
		}

		return $result;
	}

	public function getAddressById($id)
	{
		if ($record = SproutSeo_AddressRecord::model()->findByPk($id))
		{
			return SproutSeo_AddressModel::populateModel($record);
		}
		else
		{
			return new SproutSeo_AddressModel();
		}
	}

	/**
	 * @param null $id
	 *
	 * @return int
	 */
	public function deleteAddressById($id = null)
	{
		$record = new SproutFields_AddressRecord();

		return $record->deleteByPk($id);
	}

	/**
	 * @param null $id
	 *
	 * @return int
	 */
	public function deleteAddressByModelId($id = null)
	{
		$record = new SproutFields_AddressRecord();

		$attributes = [
			'modelId' => $id
		];

		return $record->deleteAllByAttributes($attributes);
	}
}