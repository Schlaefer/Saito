<?php

  App::uses('AppHelper', 'View/Helper');
  App::import('Embedly.Vendor', 'Embedly');

  class EmbedlyHelper extends AppHelper {

    public $helpers = array ( 'Html' );


    public function embedly($string) {

      $out = FALSE;

      $api = new Embedly\Embedly(
              array(
                  'user_agent' => 'Mozilla/5.0 (compatible; saito/1.0)',
                  'key' => 'a92f39e895e111e1bf614040aae4d8c9',
          ));

      $request = array(
          'urls'  => array( $string ),
          );
      $obj = array_pop($api->oembed($request));


      if ( isset($obj->html) ) :
        $out = $obj->html;
      elseif ( isset($obj->title) && isset($obj->url) ) :
        $title = $obj->title;
        $escape = TRUE;
        if ( isset($obj->thumbnail_url) ) :
          $title = $this->Html->image($obj->thumbnail_url);
          $escape = FALSE;
        endif;
        $out = $this->Html->link(
            $title,
            $obj->url,
            array( 'escape' => $escape )
            );
      endif;

      return $out;
    }

  }

?>