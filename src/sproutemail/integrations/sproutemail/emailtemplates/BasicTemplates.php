<?php

namespace barrelstrength\sproutbase\sproutemail\integrations\sproutemail\emailtemplates;

use barrelstrength\sproutbase\sproutemail\contracts\BaseEmailTemplates;
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
        return Craft::getAlias('@sproutbase/sproutemail/templates/_emailtemplates/basic');
    }
}



