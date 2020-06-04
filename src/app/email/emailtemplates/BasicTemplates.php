<?php

namespace barrelstrength\sproutbase\app\email\emailtemplates;

use barrelstrength\sproutbase\app\email\base\EmailTemplates;
use Craft;

class BasicTemplates extends EmailTemplates
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return Craft::t('sprout', 'Basic Notification (Sprout Email)');
    }

    public function getTemplateRoot(): string
    {
        return Craft::getAlias('@sproutbase/app/email/templates');
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return '_components/emailtemplates/basic';
    }
}



