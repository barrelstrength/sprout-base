<?php

namespace barrelstrength\sproutbase\integrations\emailtemplates;

use barrelstrength\sproutbase\contracts\sproutemail\BaseEmailTemplates;
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
        return Craft::getAlias('@sproutbase/templates/sproutemail/_emailtemplates/basic');
    }
}



