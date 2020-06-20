<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\integrations\sproutemail\emailtemplates\basic;

use barrelstrength\sproutbase\app\email\base\EmailTemplates;
use Craft;

class BasicSproutFormsNotification extends EmailTemplates
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return Craft::t('sprout', 'Basic Notification (Sprout Forms)');
    }

    /**
     * @return string
     */
    public function getTemplateRoot(): string
    {
        return Craft::getAlias('@sproutbase/templates/forms');
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return '_integrations/sproutemail/emailtemplates/basic';
    }
}



