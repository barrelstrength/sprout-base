<?php

namespace barrelstrength\sproutbase\integrations\emailtemplates;

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
        return Craft::t('sprout-base', 'Basic Templates (Sprout, Legacy)');
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return Craft::getAlias('@sproubase/templates/sproutemail/_emailtemplates/basic');
    }
}



