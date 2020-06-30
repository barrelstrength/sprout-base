<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\web\twig;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\web\twig\tokenparsers\SproutSeoTokenParser;
use barrelstrength\sproutbase\web\twig\variables\SproutVariable;
use craft\helpers\StringHelper;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Craft;
use Twig\TwigFilter;

class Extension extends AbstractExtension implements GlobalsInterface
{
    public function getGlobals(): array
    {
        $globals['sprout'] = new SproutVariable();

        return $globals;
    }

    public function getTokenParsers(): array
    {
        $seoSettings = SproutBase::$app->settings->getSettingsByKey('seo');

        if ($seoSettings->getIsEnabled()) {
            return [
                new SproutSeoTokenParser(),
            ];
        }

        return [];
    }

    /**
     * Makes the filters available to the template context
     *
     * @return array|TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            // Sprout Lists
            new TwigFilter('subscriberUserIds', [$this, 'subscriberUserIds'])
        ];
    }

    /**
     * Create a comma, separated list of Subscriber Element ids
     *
     * @param $subscriptions
     *
     * @return mixed
     */
    public function subscriberUserIds($subscriptions)
    {
        $subscriptionIds = $this->buildArrayOfIds($subscriptions, 'userId');

        $subscriptionIds = array_keys(array_count_values($subscriptionIds));

        return StringHelper::toString($subscriptionIds);
    }

    /**
     * Build an array of ids from our Subscriptions
     *
     * @param $subscriptions
     * @param $attribute
     *
     * @return array
     */
    public function buildArrayOfIds($subscriptions, $attribute): array
    {
        $ids = [];

        foreach ($subscriptions as $subscription) {
            if ($subscription[$attribute] !== null) {
                $ids[] = $subscription[$attribute];
            }
        }

        return $ids;
    }
}