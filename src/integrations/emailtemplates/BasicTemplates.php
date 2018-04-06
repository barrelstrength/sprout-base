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
        return Craft::t('sprout-base', 'Basic Templates (Sprout, Legacy)');
    }

    public function getBasePath()
    {
        return Craft::getAlias('@sproutbase/templates/');
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return 'sprout-base/sproutemail/_emailtemplates/basic';
    }
}



