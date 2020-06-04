<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\sitemaps\web\twig\variables;

use Craft;
use craft\base\ElementInterface;
use DateTime;
use DateTimeZone;
use Exception;

class SproutSitemapVariable
{
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
     * @param $string
     *
     * @return DateTime
     * @throws Exception
     */
    public function getDate($string): DateTime
    {
        return new DateTime($string['date'], new DateTimeZone(Craft::$app->getTimeZone()));
    }

    /**
     * @return mixed
     */
    public function getSiteIds()
    {
        return Craft::$app->getSites()->getAllSites();
    }

    /**
     * @param null $uri
     *
     * @return bool
     */
    public function uriHasTags($uri = null): bool
    {
        if (false !== strpos($uri, '{{')) {
            return true;
        }

        if (false !== strpos($uri, '{%')) {
            return true;
        }

        return false;
    }
}
