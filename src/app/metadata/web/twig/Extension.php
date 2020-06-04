<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\metadata\web\twig;

use barrelstrength\sproutbase\app\metadata\web\twig\tokenparsers\SproutSeoTokenParser;
use Twig\Extension\AbstractExtension;

class Extension extends AbstractExtension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName(): string
    {
        return 'Sprout SEO Optimize';
    }

    public function getTokenParsers(): array
    {
        return [
            new SproutSeoTokenParser()
        ];
    }

}