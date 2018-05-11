<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\fields\controllers;

use barrelstrength\sproutbase\app\fields\helpers\AddressHelper;
use barrelstrength\sproutbase\app\fields\models\Address as AddressModel;
use barrelstrength\sproutbase\app\fields\records\Address as AddressRecord;
use barrelstrength\sproutbase\SproutBase;
use craft\db\Query;
use craft\web\Controller;
use Craft;
use yii\base\Exception;
use yii\web\BadRequestHttpException;

class AddressController extends Controller
{
    /**
     * @var AddressHelper $addressHelper
     */
    protected $addressHelper;

    /**
     * Allow anonymous actions as defined within this array
     *
     * @var array
     */
    protected $allowAnonymous = ['actionGetAddressFormFields', 'actionDeleteAddress'];

    /**
     * Initialize the Address Field Helper
     */
    public function init()
    {
        $this->addressHelper = new AddressHelper();

        parent::init();
    }

    /**
     * Display the Country Input for the selected Country
     *
     * @throws Exception
     * @throws \Twig_Error_Loader
     */
    public function actionCountryInput()
    {
        $addressInfoId = Craft::$app->getRequest()->getBodyParam('addressInfoId');

        $addressInfoModel = SproutBase::$app->address->getAddressById($addressInfoId);

        $countryCode = $addressInfoModel->countryCode;

        $namespace = (Craft::$app->getRequest()->getBodyParam('namespace') != null) ? Craft::$app->getRequest()->getBodyParam('namespace') : 'address';

        $this->addressHelper->setParams($countryCode, $namespace);

        echo $this->addressHelper->countryInput();

        exit;
    }

    /**
     * Update the Address Form HTML
     *
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws \Twig_Error_Loader
     */
    public function actionChangeForm()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $countryCode = Craft::$app->getRequest()->getBodyParam('countryCode');
        $namespace = (Craft::$app->getRequest()->getBodyParam('namespace') != null) ? Craft::$app->getRequest()->getBodyParam('namespace') : 'address';

        $this->addressHelper->setParams($countryCode, $namespace);

        $html = $this->addressHelper->getAddressFormHtml();

        return $this->asJson(['html' => $html]);
    }

    /**
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws \Exception
     * @throws \Twig_Error_Loader
     */
    public function actionGetAddressFormFields()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $addressInfoId = null;

        $addressInfoModel = new AddressModel();

        if (Craft::$app->getRequest()->getBodyParam('addressInfoId') != null) {
            $addressInfoId = Craft::$app->getRequest()->getBodyParam('addressInfoId');

            $addressInfoModel = SproutBase::$app->address->getAddressById($addressInfoId);
        } elseif (Craft::$app->getRequest()->getBodyParam('defaultCountryCode') != null) {
            $defaultCountryCode = Craft::$app->getRequest()->getBodyParam('defaultCountryCode');

            $addressInfoModel->countryCode = $defaultCountryCode;
        }

        $html = $this->addressHelper->getAddressWithFormat($addressInfoModel);

        if ($addressInfoId == null) {
            $html = "";
        }

        $countryCode = $addressInfoModel->countryCode;

        $namespace = Craft::$app->getRequest()->getBodyParam('namespace') != null ? Craft::$app->getRequest()->getBodyParam('namespace') : 'address';

        $this->addressHelper->setParams($countryCode, $namespace, $addressInfoModel);

        $hiddenCountry = Craft::$app->getRequest()->getBodyParam('hideCountry') != null ? true : false;

        $countryCodeHtml = $this->addressHelper->countryInput($hiddenCountry);

        $formInputHtml = $this->addressHelper->getAddressFormHtml();

        return $this->asJson([
            'html' => $html,
            'countryCodeHtml' => $countryCodeHtml,
            'formInputHtml' => $formInputHtml,
            'countryCode' => $countryCode
        ]);
    }

    /**
     * Get an address
     *
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws \Twig_Error_Loader
     */
    public function actionGetAddress()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $result = [
            'result' => true,
            'errors' => []
        ];

        $formValues = Craft::$app->getRequest()->getBodyParam('formValues');
        $namespace = (Craft::$app->getRequest()->getBodyParam('namespace') != null) ? Craft::$app->getRequest()->getBodyParam('namespace') : 'address';

        $addressInfoModel = new AddressModel($formValues);

        if ($addressInfoModel->validate() == true) {
            $html = $this->addressHelper->getAddressWithFormat($addressInfoModel);
            $countryCode = $addressInfoModel->countryCode;

            $this->addressHelper->setParams($countryCode, $namespace, $addressInfoModel);
            $countryCodeHtml = $this->addressHelper->countryInput();
            $formInputHtml = $this->addressHelper->getAddressFormHtml();

            $result['result'] = true;

            $result['html'] = $html;
            $result['countryCodeHtml'] = $countryCodeHtml;
            $result['formInputHtml'] = $formInputHtml;
            $result['countryCode'] = $countryCode;
        } else {
            $result['result'] = false;
            $result['errors'] = $addressInfoModel->getErrors();
        }

        return $this->asJson($result);
    }

    /**
     * Delete an address
     *
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     */
    public function actionDeleteAddress()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $addressId = null;
        $addressInfoModel = null;

        if (Craft::$app->getRequest()->getBodyParam('addressInfoId') != null) {
            $addressId = Craft::$app->getRequest()->getBodyParam('addressInfoId');
            $addressInfoModel = SproutBase::$app->address->getAddressById($addressId);
        }

        $result = [
            'result' => true,
            'errors' => []
        ];

        try {
            $response = false;

            if ($addressInfoModel->id !== null && $addressInfoModel->id) {
                $addressRecord = new AddressRecord();
                $response = $addressRecord->deleteByPk($addressInfoModel->id);
            }

            $globals = (new Query())
                ->select('*')
                ->from(['{{%sproutseo_metadata_globals}}'])
                ->one();

            if ($globals && $response) {
                $identity = $globals['identity'];
                $identity = json_decode($identity, true);

                if ($identity['addressId'] != null) {
                    $identity['addressId'] = "";
                    $globals['identity'] = json_encode($identity);

                    Craft::$app->db->createCommand()->update('{{%sproutseo_metadata_globals}}',
                        $globals,
                        'id=:id',
                        [':id' => 1]
                    )->execute();
                }
            }
        } catch (Exception $e) {
            $result['result'] = false;
            $result['errors'] = $e->getMessage();
        }

        return $this->asJson($result);
    }

    /**
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     */
    public function actionQueryAddress()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $addressInfo = null;

        if (Craft::$app->getRequest()->getBodyParam('addressInfo') != null) {
            $addressInfo = Craft::$app->getRequest()->getBodyParam('addressInfo');
        }

        $result = [
            'result' => false,
            'errors' => []
        ];

        try {
            $data = [];

            if ($addressInfo) {
                $addressInfo = str_replace('\n', ' ', $addressInfo);

                // Get JSON results from this request
                $geo = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($addressInfo).'&sensor=false');

                // Convert the JSON to an array
                $geo = json_decode($geo, true);

                if ($geo['status'] === 'OK') {
                    $data['latitude'] = $geo['results'][0]['geometry']['location']['lat'];
                    $data['longitude'] = $geo['results'][0]['geometry']['location']['lng'];

                    $result = [
                        'result' => true,
                        'errors' => [],
                        'geo' => $data
                    ];
                }
            }
        } catch (\Exception $e) {
            $result['result'] = false;
            $result['errors'] = $e->getMessage();
        }

        return $this->asJson($result);
    }
}

