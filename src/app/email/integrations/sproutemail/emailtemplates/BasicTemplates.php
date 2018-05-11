<?php

namespace barrelstrength\sproutbase\app\email\integrations\sproutemail\emailtemplates;

use barrelstrength\sproutbase\app\email\contracts\BaseEmailTemplates;
use Craft;

/**
 * Class BasicTemplates
 */
class BasicTemplates extends BaseEmailTemplates
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
        return Craft::getAlias('@sproutbase/app/email/templates/_emailtemplates/basic');
    }
}



