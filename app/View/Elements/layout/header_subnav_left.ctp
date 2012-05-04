<?php
  echo $this->fetch('headerSubnavLeft');
?>
<?php
  // depracted as of CakePHP 2.1, use fetch instead
  if ( isset($headerSubnavLeft) ):
    echo $this->Html->link(
        $headerSubnavLeft['title'],
        $headerSubnavLeft['url'],
        array( 'class' => 'textlink', 'escape' => FALSE ));
  endif;
?>