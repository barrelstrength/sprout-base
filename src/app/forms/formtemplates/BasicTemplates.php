<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\formtemplates;

use barrelstrength\sproutbase\app\forms\base\FormTemplates;
use Craft;

/**
 * Class BasicTemplates
 */
class BasicTemplates extends FormTemplates
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return Craft::t('sprout', 'Basic Templates (Sprout, Legacy)');
    }

    public function getTemplateRoot(): string
    {
        return Craft::getAlias('@sproutbase/templates/forms');
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return '_components/formtemplates/basic';
    }
}



