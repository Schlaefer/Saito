<?php 
	if ( isset($headerSubnavLeft) ):
		echo $this->Html->link($headerSubnavLeft['title'], $headerSubnavLeft['url'],  array ( 'class' => 'textlink' ));
	endif;
?>