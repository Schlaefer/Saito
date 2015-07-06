<?php

namespace Embedly\View\Helper;

use Cake\View\Helper;
use Embedly\Embedly;

class EmbedlyHelper extends Helper
{

    public $helpers = ['Html'];

    protected $apiKey = null;

    /**
     * Set API-key
     *
     * @param string $apiKey API-key
     * @return void
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Process through embedly
     *
     * @param string $string string to process
     *
     * @return bool|mixed|string
     */
    public function embedly($string)
    {
        if (empty($this->apiKey)) {
            return __('Embed.ly API-key not set.');
        }

        $out = false;

        $properties = [
            'user_agent' => 'Mozilla/5.0 (compatible; cakephp/1.0)',
            'key' => $this->apiKey,
        ];
        $api = new Embedly($properties);

        try {
            $request = ['urls' => [$string]];
            $obj = current($api->oembed($request));
        } catch (\Exception $e) {
            return 'Embedding failded: ' . $e->getMessage();
        }

        if (isset($obj->html) && $obj->type !== 'link') {
            // use the html code from embedly if possible
            $out = $obj->html;
        } elseif (isset($obj->title) && isset($obj->url)) {
            // else just link to target
            $title = '';
            $escape = true;
            if (isset($obj->thumbnail_url)) {
                // use thumbnail for link if available
                $title .= $this->Html->image(
                    $obj->thumbnail_url,
                    ['class' => 'embedly-image']
                );
                $title .= $this->Html->tag('br');
                $escape = false;
            }
            $title .= $obj->title;
            $out = $this->Html->link($title, $obj->url, ['escape' => $escape]);
        }

        return $out;
    }
}
