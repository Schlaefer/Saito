<?php 
	if ( isset($headerSubnavLeft) ):
		echo $html->link($headerSubnavLeft['title'], $headerSubnavLeft['url'],  array ( 'class' => 'textlink' ));
	endif;
?>