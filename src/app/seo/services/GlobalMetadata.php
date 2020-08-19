<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\seo\services;

use barrelstrength\sproutbase\app\seo\models\Globals;
use barrelstrength\sproutbase\app\seo\records\GlobalMetadata as GlobalMetadataRecord;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Component;
use craft\base\Field;
use craft\db\Query;
use craft\errors\SiteNotFoundException;
use craft\events\SiteEvent;
use craft\fields\Assets;
use craft\fields\PlainText;
use craft\helpers\Json;
use craft\models\Site;
use DateTime;
use DateTimeZone;
use Throwable;
use yii\db\Exception;

/**
 * Class SproutSeo_GlobalMetadataService
 *
 * @package Craft
 *
 * @property array $organizationOptions
 * @property array $transforms
 */
class GlobalMetadata extends Component
{
    /**
     * Get Global Metadata values
     *
     * @param Site|null $site
     *
     * @return Globals
     * @throws SiteNotFoundException
     * @throws \yii\base\Exception
     */
    public function getGlobalMetadata($site = null): Globals
    {
        $siteId = $site->id ?? null;

        $query = (new Query())
            ->select('*')
            ->from([GlobalMetadataRecord::tableName()]);

        if ($siteId) {
            $query->where(['siteId' => $siteId]);
        } else {
            $site = Craft::$app->getSites()->getPrimarySite();
            $query->where(['siteId' => $site->id]);
        }

        $results = $query->one();

        $results['identity'] = isset($results['identity']) ? Json::decode($results['identity']) : null;
        $results['contacts'] = isset($results['contacts']) ? Json::decode($results['contacts']) : null;
        $results['ownership'] = isset($results['ownership']) ? Json::decode($results['ownership']) : null;
        $results['social'] = isset($results['social']) ? Json::decode($results['social']) : null;
        $results['robots'] = isset($results['robots']) ? Json::decode($results['robots']) : null;
        $results['settings'] = isset($results['settings']) ? Json::decode($results['settings']) : null;

        return new Globals($results);
    }

    /**
     * Save Global Metadata to database
     *
     * @param string $globalColumn
     * @param Globals $globals
     *
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function saveGlobalMetadata($globalColumn, $globals): bool
    {
        $values[$globalColumn] = $globals->getGlobalByKey($globalColumn, 'json');
        $values['siteId'] = $globals->siteId;

        $globalMetadataRecordExists = (new Query())
            ->select('*')
            ->from([GlobalMetadataRecord::tableName()])
            ->where(['[[siteId]]' => $globals->siteId])
            ->exists();

        if (!$globalMetadataRecordExists) {
            $this->insertDefaultGlobalMetadata($globals->siteId);
        }

        Craft::$app->db->createCommand()->update(GlobalMetadataRecord::tableName(),
            $values,
            ['siteId' => $globals->siteId]
        )->execute();

        return true;
    }

    /**
     * @return array
     */
    public function getTransforms(): array
    {
        $options = [
            '' => Craft::t('sprout', 'None'),
        ];

        $options[] = ['optgroup' => Craft::t('sprout', 'Default Transforms')];

        $options['sproutSeo-socialSquare'] = Craft::t('sprout', 'Square – 400x400');
        $options['sproutSeo-ogRectangle'] = Craft::t('sprout', 'Rectangle – 1200x630 – Open Graph');
        $options['sproutSeo-twitterRectangle'] = Craft::t('sprout', 'Rectangle – 1024x512 – Twitter Card');

        $transforms = Craft::$app->assetTransforms->getAllTransforms();

        if (count($transforms)) {
            $options[] = ['optgroup' => Craft::t('sprout', 'Custom Transforms')];

            foreach ($transforms as $transform) {
                $options[$transform->handle] = $transform->name;
            }
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getOrganizationOptions(): array
    {
        $jsonLdFile = Craft::getAlias('@sproutbaselib/jsonld/tree.jsonld');
        $tree = file_get_contents($jsonLdFile);

        /**
         * @var array $json
         */
        $json = json_decode($tree, true);


        /**
         * @var array $children
         */
        $children = $json['children'];

        foreach ($children as $key => $value) {
            if ($value['name'] === 'Organization') {
                $json = $value['children'];
                break;
            }
        }

        $jsonByName = [];

        foreach ($json as $key => $value) {
            $jsonByName[$value['name']] = $value;
        }

        return $jsonByName;
    }

    /**
     * @param $string
     *
     * @return DateTime
     * @throws \Exception
     */
    public function getDate($string): DateTime
    {
        return new DateTime($string['date'], new DateTimeZone(Craft::$app->getTimeZone()));
    }

    /**
     * @param $description
     *
     * @return mixed|string
     */
    public function getJsonName($description)
    {
        $name = preg_replace('/(?<!^)([A-Z])/', ' \\1', $description);

        if ($description == 'NGO') {
            $name = Craft::t('sprout', 'Non Government Organization');
        }

        return $name;
    }

    /**
     * @param $schemaType
     * @param $handle
     * @param $schemaGlobals
     *
     * @return array
     */
    public function getGlobalMetadataDropdownOptions($schemaType, $handle, $schemaGlobals): array
    {
        $options = $this->getGlobalMetadataSettingsOptions($schemaType);

        $options[] = [
            'optgroup' => Craft::t('sprout', 'Custom'),
        ];

        $schemas = $schemaGlobals->{$schemaType} != null ? $schemaGlobals->{$schemaType} : [];

        foreach ($schemas as $schema) {
            if (!$this->isCustomValue($schemaType, $schema[$handle])) {
                $options[] = ['label' => $schema[$handle], 'value' => $schema[$handle]];
            }
        }

        $options[] = ['label' => Craft::t('sprout', 'Add Custom'), 'value' => 'custom'];

        return $options;
    }

    /**
     * Returns global options given a schema type
     *
     * @param $key
     *
     * @return array
     */
    public function getGlobalMetadataSettingsOptions($key): array
    {
        $options = [];

        switch ($key) {
            case 'contacts':

                $options = [
                    [
                        'label' => Craft::t('sprout', 'Select Type...'),
                        'value' => '',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Customer Service'),
                        'value' => 'customer service',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Technical Support'),
                        'value' => 'technical support',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Billing Support'),
                        'value' => 'billing support',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Bill Payment'),
                        'value' => 'bill payment',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Sales'),
                        'value' => 'sales',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Reservations'),
                        'value' => 'reservations',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Credit Card Support'),
                        'value' => 'credit card support',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Emergency'),
                        'value' => 'emergency',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Baggage Tracking'),
                        'value' => 'baggage tracking',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Roadside Assistance'),
                        'value' => 'roadside assistance',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Package Tracking'),
                        'value' => 'package tracking',
                    ],
                ];

                break;

            case 'social':

                $options = [
                    [
                        'label' => Craft::t('sprout', 'Select...'),
                        'value' => '',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Facebook'),
                        'value' => 'Facebook',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Twitter'),
                        'value' => 'Twitter',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Google+'),
                        'value' => 'Google+',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Instagram'),
                        'value' => 'Instagram',
                    ],
                    [
                        'label' => Craft::t('sprout', 'YouTube'),
                        'value' => 'YouTube',
                    ],
                    [
                        'label' => Craft::t('sprout', 'LinkedIn'),
                        'value' => 'LinkedIn',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Myspace'),
                        'value' => 'Myspace',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Pinterest'),
                        'value' => 'Pinterest',
                    ],
                    [
                        'label' => Craft::t('sprout', 'SoundCloud'),
                        'value' => 'SoundCloud',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Tumblr'),
                        'value' => 'Tumblr',
                    ],
                ];

                break;

            case 'ownership':

                $options = [
                    [
                        'label' => Craft::t('sprout', 'Select...'),
                        'value' => '',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Bing Webmaster Tools'),
                        'value' => 'bingWebmasterTools',
                        'metaTagName' => 'msvalidate.01',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Facebook App ID'),
                        'value' => 'facebookAppId',
                        'metaTagName' => 'fb:app_id',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Facebook Page'),
                        'value' => 'facebookPage',
                        'metaTagName' => 'fb:page_id',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Facebook Admins'),
                        'value' => 'facebookAdmins',
                        'metaTagName' => 'fb:admins',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Google Search Console'),
                        'value' => 'googleSearchConsole',
                        'metaTagName' => 'google-site-verification',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Pinterest'),
                        'value' => 'pinterest',
                        'metaTagName' => 'p:domain_verify',
                    ],
                    [
                        'label' => Craft::t('sprout', 'Yandex Webmaster Tools'),
                        'value' => 'yandexWebmasterTools',
                        'metaTagName' => 'yandex-verification',
                    ],
                ];

                break;
        }

        return $options;
    }

    /**
     * Verifies on the Global Options array if option value given is custom
     *
     * @param $schemaType
     * @param $value
     *
     * @return bool
     */
    public function isCustomValue($schemaType, $value): bool
    {
        $options = SproutBase::$app->globalMetadata->getGlobalMetadataSettingsOptions($schemaType);

        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Site $site
     *
     * @return array
     * @throws SiteNotFoundException
     * @throws \yii\base\Exception
     */
    public function getPriceRangeOptions(Site $site): array
    {
        $schemaType = 'identity';

        $options = [
            [
                'label' => Craft::t('sprout', 'None'),
                'value' => '',
            ],
            [
                'label' => Craft::t('sprout', '$'),
                'value' => '$',
            ],
            [
                'label' => Craft::t('sprout', '$$'),
                'value' => '$$',
            ],
            [
                'label' => Craft::t('sprout', '$$$'),
                'value' => '$$$',
            ],
            [
                'label' => Craft::t('sprout', '$$$$'),
                'value' => '$$$$',
            ],
        ];

        $schemaGlobals = SproutBase::$app->globalMetadata->getGlobalMetadata($site);

        $priceRange = $schemaGlobals[$schemaType]['priceRange'] ?? null;

        $options[] = ['optgroup' => Craft::t('sprout', 'Custom')];

        if (!array_key_exists($priceRange, ['$' => 0, '$$' => 1, '$$$' => 2, '$$$$' => 4]) && $priceRange != '') {
            $options[] = ['label' => $priceRange, 'value' => $priceRange];
        }

        $options[] = ['label' => Craft::t('sprout', 'Add Custom'), 'value' => 'custom'];

        return $options;
    }

    /**
     * @param Site $site
     *
     * @return array|array[]
     * @throws SiteNotFoundException
     * @throws \yii\base\Exception
     */
    public function getGenderOptions(Site $site): array
    {
        $schemaType = 'identity';
        $options = [
            [
                'label' => Craft::t('sprout', 'None'),
                'value' => '',
            ],
            [
                'label' => Craft::t('sprout', 'Female'),
                'value' => 'female',
            ],
            [
                'label' => Craft::t('sprout', 'Male'),
                'value' => 'male',
            ],
        ];

        $schemaGlobals = SproutBase::$app->globalMetadata->getGlobalMetadata($site);
        $gender = $schemaGlobals[$schemaType]['gender'] ?? null;

        $options[] = ['optgroup' => Craft::t('sprout', 'Custom')];

        if (!array_key_exists($gender, ['female' => 0, 'male' => 1]) && $gender != '') {
            $options[] = ['label' => $gender, 'value' => $gender];
        }

        $options[] = ['label' => Craft::t('sprout', 'Add Custom'), 'value' => 'custom'];

        return $options;
    }

    /**
     * Returns all plain fields available given a type
     *
     * @param string $type
     * @param null $handle
     * @param null $settings
     *
     * @return array
     */
    public function getOptimizedOptions($type = PlainText::class, $handle = null, $settings = null): array
    {
        $options = [];
        $fields = Craft::$app->fields->getAllFields();

        $config = SproutBase::$app->config->getConfigByKey('seo');
        $pluginSettings = SproutBase::$app->settings->getSettingsByKey('seo');

        $options[''] = Craft::t('sprout', 'None');

        $options[] = ['optgroup' => Craft::t('sprout', 'Use Existing Field (Recommended)')];

        if ($handle == 'optimizedTitleField') {
            $options['elementTitle'] = Craft::t('sprout', 'Title');
        }

        /**
         * @var Field $field
         */
        foreach ($fields as $key => $field) {
            if (get_class($field) === $type) {
                if ($pluginSettings->displayFieldHandles) {
                    $options[$field->id] = $field->name.' – {'.$field->handle.'}';
                } else {
                    $options[$field->id] = $field->name;
                }
            }
        }

        $options[] = ['optgroup' => Craft::t('sprout', 'Add Custom Field')];
        $options['manually'] = Craft::t('sprout', 'Display Editable Field');
        $options[] = ['optgroup' => Craft::t('sprout', 'Define Custom Pattern')];

        if (!isset($options[$settings[$handle]]) && $settings[$handle] != 'manually') {
            $options[$settings[$handle]] = $settings[$handle];
        }

        $needPro = $config->getIsPro() ? '' : '(Pro)';
        $options[] = [
            'value' => 'custom',
            'label' => Craft::t('sprout', 'Add Custom Format {needPro}', [
                'needPro' => $needPro,
            ]),
            'disabled' => !$needPro,
        ];

        return $options;
    }

    /**
     * Returns keywords options
     *
     * @param string $type
     *
     * @return array
     */
    public function getKeywordsOptions($type = PlainText::class): array
    {
        $options = [];
        $fields = Craft::$app->fields->getAllFields();

        $pluginSettings = SproutBase::$app->settings->getSettingsByKey('seo');

        $options[''] = Craft::t('sprout', 'None');
        $options[] = ['optgroup' => Craft::t('sprout', 'Use Existing Field (Recommended)')];

        /** @var Field $field */
        foreach ($fields as $key => $field) {
            if (get_class($field) == $type) {
                if ($pluginSettings->displayFieldHandles) {
                    $options[$field->id] = $field->name.' – {'.$field->handle.'}';
                } else {
                    $options[$field->id] = $field->name;
                }
            }
        }

        $options[] = ['optgroup' => Craft::t('sprout', 'Add Custom Field')];

        $options['manually'] = Craft::t('sprout', 'Display Editable Field');

        return $options;
    }

    /**
     * Returns all plain fields available given a type
     *
     * @param $settings
     *
     * @return array
     */
    public function getOptimizedTitleOptions($settings): array
    {
        return $this->getOptimizedOptions(PlainText::class, 'optimizedTitleField', $settings);
    }

    /**
     * Returns all plain fields available given a type
     *
     * @param $settings
     *
     * @return array
     */
    public function getOptimizedDescriptionOptions($settings): array
    {
        return $this->getOptimizedOptions(PlainText::class, 'optimizedDescriptionField', $settings);
    }

    /**
     * Returns all plain fields available given a type
     *
     * @param $settings
     *
     * @return array
     */
    public function getOptimizedAssetsOptions($settings): array
    {
        return $this->getOptimizedOptions(Assets::class, 'optimizedImageField', $settings);
    }


    /**
     * @param $type
     * @param $metadataModel
     *
     * @return bool
     */
    public function hasActiveMetadata($type, $metadataModel): bool
    {
        switch ($type) {
            case 'search':

                if (($metadataModel['optimizedTitle'] || $metadataModel['title']) &&
                    ($metadataModel['optimizedDescription'] || $metadataModel['description'])
                ) {
                    return true;
                }

                break;

            case 'openGraph':

                if (($metadataModel['optimizedTitle'] || $metadataModel['title']) &&
                    ($metadataModel['optimizedDescription'] || $metadataModel['description']) &&
                    ($metadataModel['optimizedImage'] || $metadataModel['ogImage'])
                ) {
                    return true;
                }

                break;

            case 'twitterCard':

                if (($metadataModel['optimizedTitle'] || $metadataModel['title']) &&
                    ($metadataModel['optimizedDescription'] || $metadataModel['description']) &&
                    ($metadataModel['optimizedImage'] || $metadataModel['twitterImage'])
                ) {
                    return true;
                }

                break;
        }

        return false;
    }

    public function handleDefaultSiteMetadata(SiteEvent $event)
    {

        if (!$event->isNew) {
            return;
        }

        $seoSettings = SproutBase::$app->settings->getSettingsByKey('seo');

        if (!$seoSettings->getIsEnabled()) {
            return;
        }

        $this->insertDefaultGlobalMetadata($event->site->id);
    }

    /**
     * @param int $siteId
     *
     * @throws Exception
     */
    public function insertDefaultGlobalMetadata(int $siteId)
    {
        $defaultSettings = '{
            "seoDivider":"-",
            "defaultOgType":"website",
            "ogTransform":"sproutSeo-socialSquare",
            "twitterTransform":"sproutSeo-socialSquare",
            "defaultTwitterCard":"summary",
            "appendTitleValueOnHomepage":"",
            "appendTitleValue": ""}
        ';

        Craft::$app->getDb()->createCommand()->insert(GlobalMetadataRecord::tableName(), [
            'siteId' => $siteId,
            'identity' => null,
            'ownership' => null,
            'contacts' => null,
            'social' => null,
            'robots' => null,
            'settings' => $defaultSettings,
        ])->execute();
    }
}
