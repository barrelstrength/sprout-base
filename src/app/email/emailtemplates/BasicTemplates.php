<?php

namespace barrelstrength\sproutbase\app\email\emailtemplates;

use barrelstrength\sproutbase\app\email\base\EmailTemplates;
use Craft;

/**
 * Class BasicTemplates
 */
class BasicTemplates extends EmailTemplates
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return Craft::t('sprout-base', 'Basic Notification (Sprout Email)');
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return Craft::getAlias('@sproutbase/app/email/templates/_components/emailtemplates/basic');
    }
}



