<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\uris\sectiontypes;

use barrelstrength\sproutbase\app\uris\base\UrlEnabledSectionType;


class NoSection extends UrlEnabledSectionType
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'No Section';
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return 'none';
    }

    /**
     * @inheritDoc
     */
    public function getElementIdColumnName(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getUrlFormatIdColumnName(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getFieldLayoutSettingsObject($id)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getElementTableName(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getElementType()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getMatchedElementVariable(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getAllUrlEnabledSections($siteId): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getTableName(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function resaveElements($elementGroupId = null): bool
    {
        return true;
    }
}
