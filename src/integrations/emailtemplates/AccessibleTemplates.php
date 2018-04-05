<?php

namespace barrelstrength\sproutbase\integrations\emailtemplates;

use barrelstrength\sproutbase\contracts\sproutemail\BaseEmailTemplates;
use Craft;

/**
 * Class AccessibleTemplates
 */
class AccessibleTemplates extends BaseEmailTemplates
{
    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('sprout-base', 'Accessible Templates (Sprout, Default)');
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
        return Craft::getAlias('@sproubase/templates/sproutemail/_emailtemplates/accesible');
    }
}



