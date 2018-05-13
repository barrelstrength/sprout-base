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
    public function getName()
    {
        return Craft::t('sprout-base', 'Basic Templates (Sprout)');
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return Craft::getAlias('@sproutbase/app/email/templates/_integrations/sproutemail/emailtemplates/basic');
    }
}



