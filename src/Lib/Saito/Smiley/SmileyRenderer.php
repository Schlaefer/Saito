<?php

namespace Saito\Smiley;

use Cake\Cache\Cache as CakeCache;
use Cake\View\Helper\HtmlHelper;
use Saito\Smiley\SmileyLoader;

class SmileyRenderer
{

    const DEBUG_SMILIES_KEY = ':smilies-debug:';

    protected $_replacements;

    /**
     * @var SmileyLoader
     */
    protected $_smileyData;

    protected $_useCache;

    /**
     * @var Helper
     */
    protected $HtmlHelper;

    /**
     * Constructor
     *
     * @param array $smileyData data
     */
    public function __construct(SmileyLoader $smileyData)
    {
        $this->_smileyData = $smileyData;
    }

    /**
     * Replaces all smiley-codes in a string with appropriate HTML-tags
     *
     * @param string $string string
     * @return string
     * @throws \RuntimeException
     */
    public function replace($string)
    {
        $replacements = $this->getReplacements();
        $string = preg_replace(
            $replacements['quoted'],
            $replacements['html'],
            $string
        );
        if ($string === null) {
            throw new \RuntimeException("Can't replace smilies. 1420630983");
        }
        $string = $this->_debug($string, $replacements);

        return $string;
    }

    /**
     * Set Helper
     *
     * @param Helper $Helper helper
     * @return self
     */
    public function setHelper(HtmlHelper $Helper): self
    {
        $this->HtmlHelper = $Helper;

        return $this;
    }

    /**
     * outputs all available smilies :allSmilies:
     *
     * useful for debugging
     *
     * @param string $string string
     * @param array $replacements replacements
     * @return mixed
     */
    protected function _debug($string, $replacements)
    {
        if (strpos($string, self::DEBUG_SMILIES_KEY) === false) {
            return $string;
        }
        $smilies = $this->_smileyData->get();
        $out[] = '<table class="table table-simple">';
        $out[] = '<tr><th>Icon</th><th>Code</th><th>Image</th><th>Title</th></tr>';
        foreach ($replacements['html'] as $k => $smiley) {
            $title = $this->_l10n($smilies[$k]['title']);
            $out[] = '<tr>';
            $out[] = "<td>{$smiley}</td><td>{$smilies[$k]['code']}</td><td>{$smilies[$k]['image']}</td><td>{$title}</td>";
            $out[] = '</tr>';
        }
        $out[] = '</table>';

        return str_replace(self::DEBUG_SMILIES_KEY, implode('', $out), $string);
    }

    /**
     * Get smiley code HTML replacements.
     *
     * @return array ['codes' => [], 'quoted' => [], 'html' => []]
     */
    public function getReplacements()
    {
        if (!$this->_replacements && $this->_useCache) {
            $this->_replacements = CakeCache::read('Saito.Smilies.html');
        }
        if (!$this->_replacements) {
            $this->_replacements = ['codes' => [], 'html' => []];
            $this->_addSmilies($this->_replacements);
            $this->_pregQuote($this->_replacements);

            if ($this->_useCache) {
                CakeCache::write('Saito.Smilies.html', $this->_replacements);
            }
        }

        return $this->_replacements;
    }

    /**
     * prepares an array with smiliey-codes to be used in a preg_replace
     *
     * @param array $codes codes
     * @return void
     */
    protected function _pregQuote(array &$codes)
    {
        $delimiter = '/';
        foreach ($codes['codes'] as $key => $code) {
            $codes['quoted'][$key] = $delimiter .
                // a smiley can't be concatenated to a string and requires a
                // whitespace in front
                '(^|(?<=(\s)))' .
                preg_quote($code, $delimiter) .
                $delimiter;
        }
    }

    /**
     * Add smilies
     *
     * @param array $replacements replacements
     * @return void
     */
    protected function _addSmilies(&$replacements)
    {
        $smilies = $this->_smileyData->get();
        foreach ($smilies as $k => $smiley) {
            $replacements['codes'][] = $smiley['code'];
            $title = $this->_l10n($smiley['title']);

            //= vector font smileys
            if ($smiley['type'] === 'font') {
                $replacements['html'][$k] = $this->HtmlHelper->tag(
                    'i',
                    '',
                    [
                        'class' => "saito-smiley-font saito-smiley-{$smiley['image']}",
                        'title' => $title
                    ]
                );
                //= pixel image smileys
            } else {
                $replacements['html'][$k] = $this->HtmlHelper->image(
                    'smilies/' . $smiley['image'],
                    [
                        'alt' => $smiley['code'],
                        'class' => 'saito-smiley-image',
                        'title' => $title
                    ]
                );
            }
        }
    }

    /**
     * l10n
     *
     * @param string $string string
     * @return string|void
     */
    protected function _l10n($string)
    {
        return __d('nondynamic', $string);
    }
}
