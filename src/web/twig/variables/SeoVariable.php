<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\twig\variables;

use barrelstrength\sproutbase\app\metadata\models\Globals;
use barrelstrength\sproutbase\SproutBase;
use craft\errors\SiteNotFoundException;
use craft\models\Site;
use yii\base\Exception;

class SeoVariable
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
}
