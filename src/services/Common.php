<?php

namespace barrelstrength\sproutbase\services;

use craft\base\Component;

class Common extends Component
{
    /**
     * Call this method to get singleton
     *
     * @param bool $refresh
     *
     * @return Common
     */
    public static function Instance($refresh = false)
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new Common();
        }

        return $inst;
    }

    /**
     * @return string
     */
    public function formatErrors()
    {
        $errors = $this->getErrors();

        $text = '';
        if (!empty($errors)) {
            $text .= '<ul>';
            foreach ($errors as $key => $error) {
                if (is_array($error)) {
                    foreach ($error as $desc) {
                        $text .= '<li>'.$desc.'</li>';
                    }
                }
            }
            $text .= '</ul>';
        }

        return $text;
    }
}