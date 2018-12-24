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
use craft\helpers\Json;
use craft\web\Controller;
use Craft;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\Response;

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
    protected $allowAnonymous = [
        'action-update-address-form-html'
    ];

    /**
     * Initialize the Address Field Helper
     *
     */
    public function init()
    {
        $this->addressHelper = new AddressHelper();

        parent::init();
    }

    /**
     * Update the Address Form HTML
     *
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws \Twig_Error_Loader
     */
    public function actionUpdateAddressFormHtml(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $countryCode = Craft::$app->getRequest()->getBodyParam('countryCode');
        $namespace = Craft::$app->getRequest()->getBodyParam('namespace') ?? 'address';
        $overrideTemplatePaths = Craft::$app->getRequest()->getBodyParam('overrideTemplatePaths', false);

        $oldTemplatePath = Craft::$app->getView()->getTemplatesPath();

        if ($overrideTemplatePaths) {
            $sproutFormsTemplatePath = Craft::$app->getSession()->get('sproutforms-templatepath-fields');
            Craft::$app->getView()->setTemplatesPath($sproutFormsTemplatePath);

            // Set the base path to blank to enable Sprout Forms and Template Overrides
            $this->addressHelper->setBaseAddressFieldPath('');
        }

        $this->addressHelper->setNamespace($namespace);
        $this->addressHelper->setCountryCode($countryCode);
        $this->addressHelper->setAddressModel();

        $addressFormHtml = $this->addressHelper->getAddressFormHtml();

        if ($overrideTemplatePaths) {
            // Set the base path to blank to enable Sprout Forms and Template Overrides
            $this->addressHelper->setBaseAddressFieldPath('');

            // Set our template path back to what it was before our ajax request
            Craft::$app->getView()->setTemplatesPath($oldTemplatePath);
        }

        return $this->asJson([
            'html' => $addressFormHtml,
        ]);
    }

    /**
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws \Exception
     * @throws \Twig_Error_Loader
     */
    public function actionGetAddressFormFieldsHtml(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $addressInfoId = null;

        $addressModel = new AddressModel();

        if (Craft::$app->getRequest()->getBodyParam('addressInfoId') != null) {
            $addressInfoId = Craft::$app->getRequest()->getBodyParam('addressInfoId');

            $addressModel = SproutBase::$app->addressField->getAddressById($addressInfoId);
        } elseif (Craft::$app->getRequest()->getBodyParam('defaultCountryCode') != null) {
            $defaultCountryCode = Craft::$app->getRequest()->getBodyParam('defaultCountryCode');

            $addressModel->countryCode = $defaultCountryCode;
        }

        $addressDisplayHtml = $this->addressHelper->getAddressDisplayHtml($addressModel);

        if ($addressInfoId == null) {
            $addressDisplayHtml = '';
        }

        $countryCode = $addressModel->countryCode;

        $namespace = Craft::$app->getRequest()->getBodyParam('namespace') ?? 'address';

        $this->addressHelper->setNamespace($namespace);
        $this->addressHelper->setCountryCode($countryCode);
        $this->addressHelper->setAddressModel($addressModel);

        $showCountryDropdown = Craft::$app->getRequest()->getBodyParam('showCountryDropdown') !== null;

        $countryCodeHtml = $this->addressHelper->getCountryInputHtml($showCountryDropdown);
        $addressFormHtml = $this->addressHelper->getAddressFormHtml();

        return $this->asJson([
            'html' => $addressDisplayHtml,
            'countryCodeHtml' => $countryCodeHtml,
            'addressFormHtml' => $addressFormHtml,
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
    public function actionGetAddressDisplayHtml(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $formValues = Craft::$app->getRequest()->getBodyParam('formValues');
        $namespace = Craft::$app->getRequest()->getBodyParam('namespace') ?? 'address';

        $addressModel = new AddressModel($formValues);

        if (!$addressModel->validate()) {
            return $this->asJson([
                'result' => false,
                'errors' => $addressModel->getErrors()
            ]);
        }

        $addressDisplayHtml = $this->addressHelper->getAddressDisplayHtml($addressModel);
        $countryCode = $addressModel->countryCode;

        $this->addressHelper->setNamespace($namespace);

        if ($addressModel->fieldId) {
            $field = Craft::$app->fields->getFieldById($addressModel->fieldId);

            if (isset($field->highlightCountries) && count($field->highlightCountries)) {
                $this->addressHelper->setHighlightCountries($field->highlightCountries);
            }
        }

        $this->addressHelper->setCountryCode($countryCode);
        $this->addressHelper->setAddressModel($addressModel);

        $countryCodeHtml = $this->addressHelper->getCountryInputHtml();
        $addressFormHtml = $this->addressHelper->getAddressFormHtml();

        return $this->asJson([
            'result' => true,
            'html' => $addressDisplayHtml,
            'countryCodeHtml' => $countryCodeHtml,
            'addressFormHtml' => $addressFormHtml,
            'countryCode' => $countryCode
        ]);
    }

    /**
     * Delete an address
     *
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     */
    public function actionDeleteAddress(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $addressId = null;
        $addressModel = null;

        if (Craft::$app->getRequest()->getBodyParam('addressInfoId') != null) {
            $addressId = Craft::$app->getRequest()->getBodyParam('addressInfoId');
            $addressModel = SproutBase::$app->addressField->getAddressById($addressId);
        }

        $result = [
            'result' => true,
            'errors' => []
        ];

        try {
            $response = false;

            if ($addressModel->id !== null && $addressModel->id) {
                $addressRecord = new AddressRecord();
                $response = $addressRecord->deleteByPk($addressModel->id);
            }

            $globals = (new Query())
                ->select('*')
                ->from(['{{%sproutseo_globals}}'])
                ->one();

            if ($globals && $response) {
                $identity = $globals['identity'];
                $identity = Json::decode($identity, true);

                if ($identity['addressId'] != null) {
                    $identity['addressId'] = '';
                    $globals['identity'] = Json::encode($identity);

                    Craft::$app->db->createCommand()->update('{{%sproutseo_globals}}',
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
     * Returns the Geo Coordinates for an Address via the Google Maps service
     *
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     */
    public function actionQueryAddressCoordinatesFromGoogleMaps(): Response
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
                $geo = Json::decode($geo, true);

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

