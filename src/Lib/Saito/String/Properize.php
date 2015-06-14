<?php

namespace Saito\String;

class Properize
{

    static protected $lang;

    /**
     * Set language
     *
     * @param string $language language
     * @return void
     */
    public static function setLanguage($language)
    {
        static::$lang = $language;
    }

    /**
     * Properize
     *
     * @param string $string word to properize
     * @param string $language language
     * @throws \InvalidArgumentException
     * @return mixed $string
     */
    public static function prop($string, $language = null)
    {
        if ($language === null) {
            $language = static::$lang;
        }
        $language = explode('_', $language);
        $_method = '_properize' . ucfirst(reset($language));
        if (!method_exists(get_class(), $_method)) {
            throw new \InvalidArgumentException(
                "Properize: unknown language '$language'"
            );
        }

        return static::$_method($string);
    }

    /**
     * Properize english
     *
     * @param string $string string
     * @return string
     */
    protected static function _properizeEn($string)
    {
        $suffix = '’s';
        $apo = ['S' => 1, 's' => 1];
        if (isset($apo[mb_substr($string, -1)])) {
            $suffix = '’';
        }

        return $string . $suffix;
    }

    /**
     * Properize german
     *
     * @param string $string string
     * @return string
     */
    protected static function _properizeDe($string)
    {
        $suffix = 's';
        $apo = ['S' => 1, 's' => 1, 'ß' => 1, 'x' => 1, 'z' => 1];

        if (isset($apo[mb_substr($string, -1)])) {
            // Hans’ Tante
            $suffix = '’';
        } elseif ('ce' === (mb_substr($string, -2))) {
            // Alice’ Tante
            $suffix = '’';
        }

        return $string . $suffix;
    }
}
