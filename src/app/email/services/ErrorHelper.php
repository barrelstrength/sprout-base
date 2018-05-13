<?php

namespace barrelstrength\sproutbase\app\email\services;

use craft\base\Component;

class ErrorHelper extends Component
{
    /**
     * Call this method to get singleton
     *
     * @param bool $refresh
     *
     * @return ErrorHelper
     */
    public static function Instance($refresh = false)
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new ErrorHelper();
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