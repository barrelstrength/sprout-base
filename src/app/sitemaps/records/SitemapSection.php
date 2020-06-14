<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\sitemaps\records;

use craft\db\ActiveRecord;

/**
 * @property int id
 * @property int siteId
 * @property int urlEnabledSectionId
 * @property string type
 * @property string uri
 * @property int priority
 * @property string changeFrequency
 * @property string uniqueKey
 * @property int enabled
 */
class SitemapSection extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutseo_sitemaps}}';
    }

    /**
     * Create a new instance of the current class. This allows us to
     * properly unit test our service layer.
     *
     * @return SitemapSection
     */
    public function create(): SitemapSection
    {
        $class = get_class($this);

        return new $class();
    }
}
