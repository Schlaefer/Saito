<?php

	$xml = Xml::build(array( 'threads' => array( 'thread' => $entries) ), LIBXML_NOEMPTYTAG);
	echo $xml->saveXML();
?>