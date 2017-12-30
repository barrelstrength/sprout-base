<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\controllers;

use barrelstrength\sproutbase\helpers\AddressHelper;
use barrelstrength\sproutbase\models\sproutfields\Address as AddressModel;
use barrelstrength\sproutbase\SproutBase;
use craft\db\Query;
use craft\web\Controller;
use Craft;
use craft\web\Response;
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
     * Return all Address Form Fields for the selected Country
     *
     * @return Response
     */
    public function actionGetAddressFormFields()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $addressInfoId = null;

        if (Craft::$app->getRequest()->getBodyParam('addressInfoId') != null) {
            $addressInfoId = Craft::$app->getRequest()->getBodyParam('addressInfoId');

            $addressInfoModel = SproutBase::$app->address->getAddressById($addressInfoId);
        } else {
            $addressInfoModel = new AddressModel();

            $addressInfoModel->countryCode = $this->addressHelper->defaultCountryCode();
        }

        $html = $this->addressHelper->getAddressWithFormat($addressInfoModel);

        if ($addressInfoId == null) {
            $html = "";
        }

        $countryCode = $addressInfoModel->countryCode;

        $namespace = (Craft::$app->getRequest()->getBodyParam('namespace') != null) ? Craft::$app->getRequest()->getBodyParam('namespace') : 'address';

        $this->addressHelper->setParams($countryCode, $namespace, $addressInfoModel);

        $countryCodeHtml = $this->addressHelper->countryInput();
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
     * @throws BadRequestHttpException
     */
    public function actionGetAddress()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $result = [
            'result' => true,
            'errors' => []
        ];

        $addressInfo = Craft::$app->getRequest()->getBodyParam('addressInfo');
        $formValues = Craft::$app->getRequest()->getBodyParam('formValues');
        $namespace = (Craft::$app->getRequest()->getBodyParam('namespace') != null) ? Craft::$app->getRequest()->getBodyParam('namespace') : 'address';

        $source = '';

        if (Craft::$app->getRequest()->getBodyParam('source') != null) {
            $source = Craft::$app->getRequest()->getBodyParam('source');
        }

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

            if (isset($addressInfoModel->id) && $addressInfoModel->id) {
                $addressRecord = new SproutSeo_AddressRecord;
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
     * Find the longitude and latitude of an address
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
        } catch (Exception $e) {
            $result['result'] = false;
            $result['errors'] = $e->getMessage();
        }

        return $this->asJson($result);
    }
}

