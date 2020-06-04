<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\uris\models;

use barrelstrength\sproutbase\app\uris\base\UrlEnabledSectionType;
use Craft;
use craft\base\Element;
use craft\base\Model;
use craft\db\Query;
use craft\errors\SiteNotFoundException;

/**
 *
 * @property null|string|false $urlFormat
 * @property bool|array        $fieldLayoutObjects
 */
class UrlEnabledSection extends Model
{
    /**
     * URL-Enabled Section ID
     *
     * @var int
     */
    public $id;

    /**
     * @var UrlEnabledSectionType $type
     */
    public $type;

    /**
     * @var $sitemapSection
     */
    public $sitemapSection;

    /**
     * The current locales URL Format for this URL-Enabled Section
     *
     * @var string
     */
    public $uriFormat;

    /**
     * The Element Model for the Matched Element Variable of the current page load
     *
     * @var Element
     */
    public $element;

    /**
     * Name of the Url Enabled Element Group
     *
     * @var string
     */
    public $name;

    /**
     * Handle of the Url Enabled Element Group
     *
     * @var string
     */
    public $handle;

    /**
     * Get the URL format from the element via the Element Group integration
     *
     * @return false|null|string
     * @throws SiteNotFoundException
     */
    public function getUrlFormat()
    {
        $primarySite = Craft::$app->getSites()->getPrimarySite();

        $urlEnabledSectionUrlFormatTableName = $this->type->getTableName();
        $urlEnabledSectionUrlFormatColumnName = $this->type->getUrlFormatColumnName();
        $urlEnabledSectionIdColumnName = $this->type->getUrlFormatIdColumnName();

        $query = (new Query())
            ->select($urlEnabledSectionUrlFormatColumnName)
            ->from(["{{%$urlEnabledSectionUrlFormatTableName}}"])
            ->where([$urlEnabledSectionIdColumnName => $this->id]);

        if ($this->type->isLocalized()) {
            $query->andWhere(['siteId' => $primarySite->id]);
        }

        if ($query->scalar()) {
            $this->uriFormat = $query->scalar();
        }

        return $this->uriFormat;
    }

    /**
     * @param bool $matchAll
     *
     * @return bool
     */
    public function hasElementMetadataField($matchAll = true): bool
    {
        $fieldLayoutObjects = $this->getFieldLayoutObjects();

        if ($fieldLayoutObjects === false) {
            return false;
        }

        $totalFieldLayouts = count($fieldLayoutObjects);
        $totalElementMetaFields = 0;

        // We want to make sure there is an Element Metadata field on every field layout object.
        // For example, a Category Group or Product Type just needs one Element Metadata for its Field Layout.
        // A section with multiple Entry Types needs an Element Metadata field on each of it's Field Layouts.
        foreach ($fieldLayoutObjects as $fieldLayoutObject) {
            $fields = $fieldLayoutObject->getFieldLayout()->getFields();

            foreach ($fields as $field) {
                if (get_class($field) == 'barrelstrength\sproutseo\fields\ElementMetadata') {
                    $totalElementMetaFields++;
                }
            }
        }

        if ($matchAll) {
            // If we have an equal number of Element Metadata fields,
            // the setup is optimized to handle metadata at each level
            // We use this to indicate to the user if everything is setup
            if ($totalElementMetaFields >= $totalFieldLayouts) {
                return true;
            }
        } else if ($totalElementMetaFields > 0) {
            // When we're resaving our elements, we don't care if everything is
            // setup, we just need to know if any Element Metadata Fields exist
            // and need updating.
            return true;
        }

        return false;
    }

    /**
     * @param $fieldLayoutId
     *
     * @return bool
     * @return bool
     */
    public function hasFieldLayoutId($fieldLayoutId): bool
    {
        $fieldLayoutObjects = $this->getFieldLayoutObjects();

        if ($fieldLayoutObjects === false) {
            return false;
        }

        foreach ($fieldLayoutObjects as $fieldLayoutObject) {
            $fieldLayout = $fieldLayoutObject->getFieldLayout();

            if ($fieldLayout->id == $fieldLayoutId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array|bool
     */
    private function getFieldLayoutObjects()
    {
        $fieldLayoutObjects = $this->type->getFieldLayoutSettingsObject($this->id);

        if (!$fieldLayoutObjects) {
            return false;
        }

        // Make what we get back into an array
        if (!is_array($fieldLayoutObjects)) {
            $fieldLayoutObjects = [$fieldLayoutObjects];
        }

        return $fieldLayoutObjects;
    }
}
