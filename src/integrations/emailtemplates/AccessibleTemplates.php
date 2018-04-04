<?php

namespace barrelstrength\sproutbase\integrations\emailtemplates;

use barrelstrength\sproutbase\sproutemail\contracts\BaseEmailTemplates;
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

    /**
     * @return string
     */
    public function getPath()
    {
        return Craft::getAlias('@sproubase/templates/sproutemail/_emailtemplates/accesible');
    }
}



