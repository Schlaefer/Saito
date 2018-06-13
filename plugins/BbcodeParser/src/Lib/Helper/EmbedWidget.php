<?php

namespace Plugin\BbcodeParser\src\Lib\Helper;

use Cake\Cache\Cache;
use Embed\Embed;

class EmbedWidget
{
    private $url;

    private $helpers;

    public function __construct(string $url, $helpers)
    {
        $this->url = $url;
        $this->helpers = $helpers;
    }

    public function render()
    {
        $url = $this->url;
        $renderer = function () use ($url) {
            try {
                $info = \Embed\Embed::create(
                    $url,
                [
                    'min_image_width' => 100,
                    'min_image_height' => 100,
                ]
            );
            } catch (\Throwable $e) {
                return $this->helpers->Html->link($url, $url, ['target' => '_blank']);
            }

            $code = $info->code;
            if ($code) {
                return $code;
            }

            $html = [];

            $image = $info->image;
            if ($image) {
                $html[] = $this->helpers->Html->image($image, ['class' => 'card-img-top']);
            }

            $body = [];
            $title = $info->title;
            $link = $info->url;
            if ($title) {
                $title = $this->helpers->Html->tag('h5', h($title));
                $body[] = $this->helpers->Html->link(
                    $title,
                    $link,
                    ['class' => 'card-title', 'escape' => false, 'target' => '_blank']
                );
            }

            $description = $info->description;
            if ($description) {
                $body[] = $this->helpers->Html->para('card-text', $description);
            }

            $providerName = $info->providerName;
            if ($providerName) {
                $provider = $providerName;

                $providerUrl = $info->providerUrl;
                if ($providerUrl) {
                    $provider = $this->helpers->Html->link($provider, $providerUrl);
                }

                $providerIcon = $info->providerIcon;
                if ($providerIcon) {
                    $icon = $this->helpers->Html->image(
                        $providerIcon,
                        ['class' => 'richtext-embed-provider-icon']
                    );
                    $provider = $icon . $provider;
                }

                $body[] = $this->helpers->Html->div('richtext-embed-provider', $provider);
            }

            // add link on bottom if no link title is emitted
            if (!$title) {
                $body[] = $this->helpers->Html->link(
                    $link,
                    $link,
                    ['class' => 'richtext-embed-url', 'target' => '_blank']
                );
            }

            $html[] = $this->helpers->Html->div('card-body', implode("\n", $body));

            return $this->helpers->Html->div('card richtext-embed', implode("\n", $html));

        };

        // return $renderer();

        return Cache::remember('embed-' . md5($this->url), $renderer, 'bbcodeParserEmbed');
    }
}
