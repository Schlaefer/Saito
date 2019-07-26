<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace SaitoSearch\Lib;

class SimpleSearchString
{

    /**
     * Minimum search word length
     *
     * @var integer
     */
    protected $_length = 4;

    /**
     * Operators for simple search
     *
     * @var string
     */
    protected $_operators = '+-';

    /**
     * Search string
     *
     * @var string
     */
    protected $_string;

    /**
     * Constructor.
     *
     * @param string $string string
     */
    public function __construct($string)
    {
        $this->_string = $string;
    }

    /**
     * Checks if search words have minimal word length
     *
     * @return bool
     */
    public function validateLength()
    {
        if (empty($this->_string)) {
            return false;
        }

        return strlen($this->getOmittedWords()) === 0;
    }

    /**
     * Sets minimum search-word-length.
     *
     * @param int $minLength minimum search word length
     * @return self
     */
    public function setMinWordLength(int $minLength): self
    {
        $this->_length = $minLength;

        return $this;
    }

    /**
     * Gets minimum search-word-length
     *
     * @return int
     */
    public function getMinWordLength(): int
    {
        return $this->_length;
    }

    /**
     * get ommited words
     *
     * @return string
     */
    public function getOmittedWords()
    {
        $filter = function ($word) {
            return mb_strlen($word) < $this->_length;
        };
        $string = preg_replace('/(".*")/', '', $this->_string);
        $words = $this->_split($string);
        $result = array_filter($words, $filter);

        return implode(' ', $result);
    }

    /**
     * split string
     *
     * @param string $string string
     * @return array
     */
    protected function _split($string)
    {
        return preg_split(
            "/(^|\s+)[{$this->_operators}]?/",
            $string,
            -1,
            PREG_SPLIT_NO_EMPTY
        );
    }

    /**
     * replaces whitespace with '+'
     *
     * whitespace should imply AND (not OR)
     *
     * @return string
     */
    public function replaceOperators()
    {
        $string = $this->_fulltextHyphenFix($this->_string);

        return ltrim(preg_replace('/(^|\s)(?![-+><])/i', ' +', $string));
    }

    /**
     * hyphen fix
     *
     * @see: http://bugs.mysql.com/bug.php?id=2095
     *
     * @param string $string string
     * @return string
     */
    protected function _fulltextHyphenFix($string)
    {
        $words = $this->_split($string);
        foreach ($words as $word) {
            $first = mb_substr($word, 0, 1);
            if ($first === '"') {
                continue;
            }
            if (mb_strpos($word, '-') === false) {
                continue;
            }
            $string = str_replace($word, '"' . $word . '"', $string);
        }

        return $string;
    }
}
