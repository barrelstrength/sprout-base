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
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Craft;

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
}