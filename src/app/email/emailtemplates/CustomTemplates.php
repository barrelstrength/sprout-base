<?php

namespace barrelstrength\sproutbase\app\email\emailtemplates;

use barrelstrength\sproutbase\app\email\base\EmailTemplates;
use Craft;

/**
 * The Custom Templates is used to dynamically create an EmailTemplate
 * integration when a user selects the custom option and provides a path
 * to the custom templates they wish to use.
 *
 * The Custom Templates integration is not registered with Sprout Email
 * and will not display in the Email Templates dropdown list.
 */
class CustomTemplates extends EmailTemplates
{
    /**
     * @var string
     */
    private $_path = null;

    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('sprout-base', 'Custom Templates');
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->_path = $path;
    }
}



