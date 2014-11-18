<?php

namespace Saito\String;

class Properize
{

    static protected $lang;

    public static function setLanguage($language)
    {
        static::$lang = $language;
    }

    /**
     * @param $string word to properize
     * @param string $language
     * @throws \InvalidArgumentException
     * @return mixed $string
     */
    public static function prop($string, $language = null)
    {
        if ($language === null) {
            $language = static::$lang;
        }
        $language = explode('_', $language);
        $_method = 'properize' . ucfirst(reset($language));
        if (!method_exists(get_class(), $_method)) {
            throw new \InvalidArgumentException("Properize: unknown language '$language'");
        }
        return static::$_method($string);
    }

    protected static function properizeEn($string)
    {
        $suffix = '’s';
        $apo = ['S' => 1, 's' => 1];
        if (isset($apo[mb_substr($string, -1)])) {
            $suffix = '’';
        }
        return $string . $suffix;
    }

    protected static function properizeDe($string)
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
