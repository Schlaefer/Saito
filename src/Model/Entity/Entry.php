<?php

namespace App\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Saito\App\Registry;
use Saito\Plugin;
use Saito\Posting\Basic\BasicPostingInterface;
use Saito\Posting\Basic\BasicPostingTrait;
use Saito\Posting\PostingInterface;

class Entry extends Entity implements BasicPostingInterface
{
    use BasicPostingTrait;

    /**
     * Mutator for "text" property
     *
     * @param string $text content for "text"
     * @return string
     */
    //@codingStandardsIgnoreStart
    public function _setText(string $text)
    {
    //@codingStandardsIgnoreEnd
        if (empty($text)) {
            return $text;
        }

        //// sends text through markup preprocessor
        $markupSettings = Configure::read('Saito.Settings.Parser');
        if ($markupSettings) {
            $Preprocessor = Plugin::getParserClassInstance('Preprocessor', $markupSettings);
            $text = $Preprocessor->process($text);
        }

        return $text;
    }

    /**
     * Convert entity to posting
     *
     * @return PostingInterface
     */
    public function toPosting()
    {
        return Registry::newInstance(
            '\Saito\Posting\Posting',
            ['rawData' => $this->toArray()]
        );
    }
}
