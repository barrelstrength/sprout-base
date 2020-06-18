<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\metadata\services;

use barrelstrength\sproutbase\app\metadata\base\Schema as BaseSchema;
use barrelstrength\sproutbase\app\metadata\events\RegisterSchemasEvent;
use barrelstrength\sproutbase\app\metadata\schema\ContactPointSchema;
use barrelstrength\sproutbase\app\metadata\schema\CreativeWorkSchema;
use barrelstrength\sproutbase\app\metadata\schema\EventSchema;
use barrelstrength\sproutbase\app\metadata\schema\GeoSchema;
use barrelstrength\sproutbase\app\metadata\schema\ImageObjectSchema;
use barrelstrength\sproutbase\app\metadata\schema\IntangibleSchema;
use barrelstrength\sproutbase\app\metadata\schema\MainEntityOfPageSchema;
use barrelstrength\sproutbase\app\metadata\schema\OrganizationSchema;
use barrelstrength\sproutbase\app\metadata\schema\PersonSchema;
use barrelstrength\sproutbase\app\metadata\schema\PlaceSchema;
use barrelstrength\sproutbase\app\metadata\schema\PostalAddressSchema;
use barrelstrength\sproutbase\app\metadata\schema\ProductSchema;
use barrelstrength\sproutbase\app\metadata\schema\ThingSchema;
use barrelstrength\sproutbase\app\metadata\schema\WebsiteIdentityOrganizationSchema;
use barrelstrength\sproutbase\app\metadata\schema\WebsiteIdentityPersonSchema;
use barrelstrength\sproutbase\app\metadata\schema\WebsiteIdentityPlaceSchema;
use barrelstrength\sproutbase\app\metadata\schema\WebsiteIdentityWebsiteSchema;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\helpers\Json;
use yii\base\Component;

/**
 *
 * @property array $schemasTypes
 * @property array $schemaOptions
 */
class SchemaMetadata extends Component
{
    const EVENT_REGISTER_SCHEMAS = 'registerSchemasEvent';

    /**
     * Full schema.org core and extended vocabulary as described on schema.org
     * http://schema.org/docs/full.html
     *
     * @var array
     */
    public $vocabularies = [];

    /**
     * All registered Schema Types
     *
     * @var array
     */
    protected $schemaTypes = [];

    /**
     * All instantiated Schema Types indexed by class
     *
     * @var BaseSchema[]
     */
    protected $schemas = [];

    /**
     * @return array
     */
    public function getSchemasTypes(): array
    {
        $schemas = [
            WebsiteIdentityOrganizationSchema::class,
            WebsiteIdentityPersonSchema::class,
            WebsiteIdentityWebsiteSchema::class,
            WebsiteIdentityPlaceSchema::class,
            ContactPointSchema::class,
            ImageObjectSchema::class,
            MainEntityOfPageSchema::class,
            PostalAddressSchema::class,
            GeoSchema::class,
            ThingSchema::class,
            CreativeWorkSchema::class,
            EventSchema::class,
            IntangibleSchema::class,
            OrganizationSchema::class,
            PersonSchema::class,
            PlaceSchema::class
        ];

        if (Craft::$app->getPlugins()->getPlugin('commerce')) {
            $schemas[] = ProductSchema::class;
        }

        $event = new RegisterSchemasEvent([
            'schemas' => $schemas
        ]);

        $this->trigger(self::EVENT_REGISTER_SCHEMAS, $event);

        foreach ($event->schemas as $schema) {
            $this->schemaTypes[] = $schema;
        }

        return $this->schemaTypes;
    }

    /**
     * @return BaseSchema[]
     */
    public function getSchemas(): array
    {
        $schemaTypes = $this->getSchemasTypes();

        foreach ($schemaTypes as $schemaClass) {
            $schema = new $schemaClass();
            $this->schemas[$schemaClass] = $schema;
        }

        uasort($this->schemas, static function($a, $b) {
            /**
             * @var $a BaseSchema
             * @var $b BaseSchema
             */
            return $a->getName() <=> $b->getName();
        });

        return $this->schemas;
    }

    /**
     * Returns a list of available schema maps for display in a Main Entity select field
     *
     * @return array
     */
    public function getSchemaOptions(): array
    {
        $schemas = $this->getSchemas();

        foreach ($schemas as $schemaClass => $schema) {
            if ($schema->isUnlistedSchemaType()) {
                unset($schemas[$schemaClass]);
            }
        }

        // Get a filtered list of our default Sprout SEO schema
        $defaultSchema = array_filter($schemas, static function($map) {
            /**
             * @var SchemaMetadata $map
             */
            return stripos(get_class($map), 'barrelstrength\\sproutseo') !== false;
        });

        // Get a filtered list of of any custom schema
        $customSchema = array_filter($schemas, static function($map) {
            /**
             * @var SchemaMetadata $map
             */
            return stripos(get_class($map), 'barrelstrength\\sproutseo') === false;
        });

        // Build our options
        $schemaOptions = [
            '' => Craft::t('sprout', 'None'), [
                'optgroup' => Craft::t('sprout', 'Default Types')
            ]
        ];


        $schemaOptions = array_merge($schemaOptions, array_map(static function($schema) {
            /**
             * @var BaseSchema $schema
             */
            return [
                'label' => $schema->getName(),
                'type' => $schema->getType(),
                'value' => get_class($schema)
            ];
        }, $defaultSchema));

        if (count($customSchema)) {
            $schemaOptions[] = ['optgroup' => Craft::t('sprout', 'Custom Types')];

            $schemaOptions = array_merge($schemaOptions, array_map(static function($schema) {
                /**
                 * @var BaseSchema $schema
                 */
                return [
                    'label' => $schema->getName(),
                    'type' => $schema->getType(),
                    'value' => get_class($schema),
                    'isCustom' => '1'
                ];
            }, $customSchema));
        }

        return $schemaOptions;
    }

    /**
     * Prepare an array of the optimized Meta
     *
     * @param array $schemas
     *
     * @return array[][]
     */
    public function getSchemaSubtypes($schemas): array
    {
        $values = [];

        foreach ($schemas as $schema) {
            if (isset($schema['type'])) {
                $type = $schema['type'];

                // Create a generic first item in our list that matches the top level schema
                // We do this so we don't have a blank dropdown option for our secondary schemas
                $firstItem = [
                    $type => []
                ];

                if (!isset($schema['isCustom'])) {
                    $values[$schema['value']] = $this->getSchemaChildren($type);

                    if (count($values[$schema['value']])) {
                        $values[$schema['value']] = array_merge($firstItem, $values[$schema['value']]);
                    }
                }
            }
        }

        return $values;
    }

    /**
     * Returns a schema map instance (based on $uniqueKey) or $default
     *
     * @param string $uniqueKey
     * @param null $default
     *
     * @return BaseSchema|null
     */
    public function getSchemaByUniqueKey($uniqueKey, $default = null)
    {
        $this->getSchemas();

        return array_key_exists($uniqueKey, $this->schemas) ? $this->schemas[$uniqueKey] : $default;
    }

    /**
     * Returns an array of vocabularies based on the path provided
     * SproutBase::$app->schema->getVocabularies('Organization.LocalBusiness.AutomotiveBusiness');
     *
     * @param null $path
     *
     * @return array
     */
    public function getVocabularies($path = null): array
    {
        $jsonLdTreePath = Craft::getAlias('@sproutbaselib/jsonld/tree.jsonld');

        $allVocabularies = Json::decode(file_get_contents($jsonLdTreePath));

        $this->vocabularies = $this->updateArrayKeys($allVocabularies['children'], 'name');

        if ($path) {
            return $this->getArrayByPath($this->vocabularies, $path);
        }

        return $this->vocabularies;
    }

    /**
     * @param        $array
     * @param        $path
     * @param string $separator
     *
     * @return mixed
     */
    protected function getArrayByPath($array, $path, $separator = '.')
    {
        $keys = explode($separator, $path);

        $level = 1;
        foreach ($keys as $key) {
            if ($level == 1) {
                $array = $array[$key];
            } else {
                $array = $array['children'][$key];
            }

            $level++;
        }

        return $array;
    }

    /**
     * @param array $oldArray
     * @param       $replaceKey
     *
     * @return array
     */
    protected function updateArrayKeys(array $oldArray, $replaceKey): array
    {
        $newArray = [];

        foreach ($oldArray as $key => $value) {
            if (isset($value[$replaceKey])) {
                $key = $value[$replaceKey];
            }

            if (is_array($value)) {
                $value = $this->updateArrayKeys($value, $replaceKey);
            }

            $newArray[$key] = $value;
        }

        return $newArray;
    }

    /**
     * @param $type
     *
     * @return array
     */
    private function getSchemaChildren($type): array
    {
        $tree = SproutBase::$app->schemaMetadata->getVocabularies($type);

        /**
         * @var array $children
         */
        $children = $tree['children'] ?? [];

        // let's assume 3 levels
        if (count($children)) {
            foreach ($children as $key => $level1) {
                $children[$key] = [];

                /**
                 * @var array $level1children
                 */
                $level1children = $level1['children'] ?? [];

                if (count($level1children)) {
                    foreach ($level1children as $key2 => $level2) {
                        $children[$key][$key2] = [];

                        /**
                         * @var array $level2children
                         */
                        $level2children = $level2['children'] ?? [];

                        if (count($level2children)) {
                            foreach ($level2children as $key3 => $level3) {
                                $children[$key][$key2][] = $key3;
                            }
                        }
                    }
                }
            }
        }

        return $children;
    }
}
