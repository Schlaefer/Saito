<?php

  App::uses('AppHelper', 'View/Helper');
  App::import('vendor', 'Embedly.Embedly');

  class EmbedlyHelper extends AppHelper {

    public $helpers = array( 'Html' );

    protected static $_apiKey = NULL;

    public function setApiKey($apiKey) {
      self::$_apiKey = $apiKey;
    }

    public function embedly($string) {

      if ( self::$_apiKey == FALSE ) :
        return __('Embed.ly API-key not set.');
      endif;

      $out = FALSE;

      $api = new Embedly\Embedly(
              array(
                  'user_agent' => 'Mozilla/5.0 (compatible; cakephp/1.0)',
                  'key' => self::$_apiKey,
          ));

			$request = ['urls' => [$string]];

			try {
				$obj = current($api->oembed($request));
			} catch (Exception $e) {
				return 'Embedding failded: ' . $e->getMessage();
			}

      if ( isset($obj->html) ):
        // use the html code from embedly if possible
        $out = $obj->html;
      elseif ( isset($obj->title) && isset($obj->url) ):
        // else just link to target
				$title = '';
        $escape = TRUE;
        if ( isset($obj->thumbnail_url) ) :
          // use thumbnail for link if available
          $title .= $this->Html->image($obj->thumbnail_url, array(
							'class' => 'embedly-image'
					));
					$title .= $this->Html->tag('br');
          $escape = FALSE;
        endif;
        $title .= $obj->title;
        $out = $this->Html->link(
            $title, $obj->url, array( 'escape' => $escape )
        );
      endif;

      return $out;
    }

  }

?>