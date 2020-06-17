<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\twig\variables;

use barrelstrength\sproutbase\app\metadata\models\Globals;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\elements\Asset;
use craft\errors\SiteNotFoundException;
use craft\fields\Assets;
use craft\fields\PlainText;
use craft\models\Site;
use DateTime;
use DateTimeZone;
use yii\base\Exception;

class SproutSeoVariable
{
    /**
     * Sets SEO metadata in templates
     *
     * @param array $meta Array of supported meta values
     */
    public function meta(array $meta = [])
    {
        if (count($meta)) {
            SproutBase::$app->optimizeMetadata->updateMeta($meta);
        }
    }

    /**
     * @param null $site
     *
     * @return string
     * @throws Exception
     * @throws SiteNotFoundException
     */
    public function getDivider($site = null): string
    {
        $globals = SproutBase::$app->globalMetadata->getGlobalMetadata($site);
        $divider = '';

        if (isset($globals['settings']['seoDivider'])) {
            $divider = $globals->settings['seoDivider'];
        }

        return $divider;
    }

    /**
     * @param Site|null $site
     *
     * @return Globals
     * @throws Exception
     * @throws SiteNotFoundException
     */
    public function getGlobalMetadata(Site $site = null): Globals
    {
        return SproutBase::$app->globalMetadata->getGlobalMetadata($site);
    }

    /**
     * @return mixed
     */
    public function getAssetElementType()
    {
        return Asset::class;
    }

    /**
     * @param $id
     *
     * @return ElementInterface|null
     */
    public function getElementById($id)
    {
        $element = Craft::$app->elements->getElementById($id);

        return $element != null ? $element : null;
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
     * Returns global options given a schema type
     *
     * @param $schemaType
     *
     * @return array
     */
    public function getGlobalOptions($schemaType): array
    {
        $options = [];

        switch ($schemaType) {
            case 'contacts':

                $options = [
                    [
                        'label' => Craft::t('sprout', 'Select Type...'),
                        'value' => ''
                    ],
                    [
                        'label' => Craft::t('sprout', 'Customer Service'),
                        'value' => 'customer service'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Technical Support'),
                        'value' => 'technical support'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Billing Support'),
                        'value' => 'billing support'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Bill Payment'),
                        'value' => 'bill payment'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Sales'),
                        'value' => 'sales'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Reservations'),
                        'value' => 'reservations'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Credit Card Support'),
                        'value' => 'credit card support'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Emergency'),
                        'value' => 'emergency'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Baggage Tracking'),
                        'value' => 'baggage tracking'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Roadside Assistance'),
                        'value' => 'roadside assistance'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Package Tracking'),
                        'value' => 'package tracking'
                    ]
                ];

                break;

            case 'social':

                $options = [
                    [
                        'label' => Craft::t('sprout', 'Select...'),
                        'value' => ''
                    ],
                    [
                        'label' => Craft::t('sprout', 'Facebook'),
                        'value' => 'Facebook'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Twitter'),
                        'value' => 'Twitter'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Google+'),
                        'value' => 'Google+'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Instagram'),
                        'value' => 'Instagram'
                    ],
                    [
                        'label' => Craft::t('sprout', 'YouTube'),
                        'value' => 'YouTube'
                    ],
                    [
                        'label' => Craft::t('sprout', 'LinkedIn'),
                        'value' => 'LinkedIn'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Myspace'),
                        'value' => 'Myspace'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Pinterest'),
                        'value' => 'Pinterest'
                    ],
                    [
                        'label' => Craft::t('sprout', 'SoundCloud'),
                        'value' => 'SoundCloud'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Tumblr'),
                        'value' => 'Tumblr'
                    ]
                ];

                break;

            case 'ownership':

                $options = [
                    [
                        'label' => Craft::t('sprout', 'Select...'),
                        'value' => ''
                    ],
                    [
                        'label' => Craft::t('sprout', 'Bing Webmaster Tools'),
                        'value' => 'bingWebmasterTools',
                        'metaTagName' => 'msvalidate.01'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Facebook App ID'),
                        'value' => 'facebookAppId',
                        'metaTagName' => 'fb:app_id'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Facebook Page'),
                        'value' => 'facebookPage',
                        'metaTagName' => 'fb:page_id'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Facebook Admins'),
                        'value' => 'facebookAdmins',
                        'metaTagName' => 'fb:admins'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Google Search Console'),
                        'value' => 'googleSearchConsole',
                        'metaTagName' => 'google-site-verification'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Pinterest'),
                        'value' => 'pinterest',
                        'metaTagName' => 'p:domain_verify'
                    ],
                    [
                        'label' => Craft::t('sprout', 'Yandex Webmaster Tools'),
                        'value' => 'yandexWebmasterTools',
                        'metaTagName' => 'yandex-verification'
                    ]
                ];

                break;
        }

        return $options;
    }

    /**
     * @param $schemaType
     * @param $handle
     * @param $schemaGlobals
     *
     * @return array
     */
    public function getFinalOptions($schemaType, $handle, $schemaGlobals): array
    {
        $options = $this->getGlobalOptions($schemaType);

        $options[] = [
            'optgroup' => Craft::t('sprout', 'Custom')
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
     * Verifies on the Global Options array if option value given is custom
     *
     * @param $schemaType
     * @param $value
     *
     * @return bool
     */
    public function isCustomValue($schemaType, $value): bool
    {
        $options = $this->getGlobalOptions($schemaType);

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
     * @throws Exception
     */
    public function getPriceRangeOptions(Site $site): array
    {
        $schemaType = 'identity';

        $options = [
            [
                'label' => Craft::t('sprout', 'None'),
                'value' => ''
            ],
            [
                'label' => Craft::t('sprout', '$'),
                'value' => '$'
            ],
            [
                'label' => Craft::t('sprout', '$$'),
                'value' => '$$'
            ],
            [
                'label' => Craft::t('sprout', '$$$'),
                'value' => '$$$'
            ],
            [
                'label' => Craft::t('sprout', '$$$$'),
                'value' => '$$$$'
            ]
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
     * @return array
     * @throws SiteNotFoundException
     * @throws Exception
     */
    public function getGenderOptions(Site $site): array
    {
        $schemaType = 'identity';
        $options = [
            [
                'label' => Craft::t('sprout', 'None'),
                'value' => ''
            ],
            [
                'label' => Craft::t('sprout', 'Female'),
                'value' => 'female'
            ],
            [
                'label' => Craft::t('sprout', 'Male'),
                'value' => 'male',
            ]
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

        $pluginSettings = SproutBase::$app->settings->getSettingsByKey('metadata');

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

        // @todo - migration, fix editions/pro logic
//        $needPro = $this->getIsPro() ? '' : '(Pro)';
        $needPro = true;
        $options[] = [
            'value' => 'custom',
            'label' => Craft::t('sprout', 'Add Custom Format {needPro}', [
                'needPro' => $needPro
            ]),
            'disabled' => !$needPro
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

        $pluginSettings = SproutBase::$app->settings->getSettingsByKey('metadata');

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
     * Returns registerSproutSeoSchemas hook
     *
     * @return array
     */
    public function getSchemas(): array
    {
        return SproutBase::$app->schema->getSchemas();
    }

    /**
     * Returns global contacts
     *
     * @param Site|null $currentSite
     *
     * @return array
     * @throws Exception
     * @throws SiteNotFoundException
     */
    public function getContacts(Site $currentSite = null): array
    {
        $contacts = SproutBase::$app->globalMetadata->getGlobalMetadata($currentSite)->contacts;

        $contacts = $contacts ?: [];

        foreach ($contacts as &$contact) {
            $contact['type'] = $contact['contactType'];
            unset($contact['contactType']);
        }

        return $contacts;
    }

    /**
     * Returns global social profiles
     *
     * @param Site|null $currentSite
     *
     * @return array
     * @throws Exception
     * @throws SiteNotFoundException
     */
    public function getSocialProfiles(Site $currentSite = null): array
    {
        $socials = SproutBase::$app->globalMetadata->getGlobalMetadata($currentSite)->social;

        $socials = $socials ?: [];

        foreach ($socials as &$social) {
            $social['name'] = $social['profileName'];
            unset($social['profileName']);
        }

        return $socials;
    }

    /**
     * Prepare an array of the image transforms available
     *
     * @return array
     */
    public function getTransforms(): array
    {
        return SproutBase::$app->globalMetadata->getTransforms();
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

    /**
     * @return int
     */
    public function getDescriptionLength(): int
    {
        return SproutBase::$app->elementMetadata->getDescriptionLength();
    }

    /**
     * @param null $uri
     *
     * @return bool
     */
    public function uriHasTags($uri = null): bool
    {
        if (strpos($uri, '{{') !== false) {
            return true;
        }

        if (strpos($uri, '{%') !== false) {
            return true;
        }

        return false;
    }
}
