<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace MailObfuscator\View\Helper;

use Cake\View\Helper;

/**
 * Mail Obfuscator Helper
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 */
class MailObfuscatorHelper extends Helper
{
    public $helpers = ['Html'];

    /**
     * Generate mail lLink
     *
     * @param string $addr mail address
     * @param string $title link title
     *
     * @return string
     */
    public function link($addr, $title = '')
    {
        $hasTitle = 0;
        if (empty($title) === false) {
            $hasTitle = 1;
        }
        $rand = "moh_" . md5(mt_rand(1, 10000) . $addr);
        [$ttl, $dom] = explode('@', $addr);

        // missing  style='unicode-bidi:bidi-override;direction:rtl;'
        $mailto = '<a id="' . $rand . '" href="#" data-ttl="' . $ttl . '" data-dom="' . $dom . '">' . $title . '</a>';
        $mailto .= '<noscript><p>[You need to have Javascript enabled to see this mail address.]</p></noscript>';
        $mailto .= $this->Html->scriptBlock(
            "$(function(){
						var el = $('#$rand'),
								mt = el.data('ttl') + '@' + el.data('dom');
						el.attr('href', 'mai' + 'lto:' + mt);
						if ($hasTitle === 0) { el.html(mt); }
					})
			"
        );

        return $mailto;
    }
}
