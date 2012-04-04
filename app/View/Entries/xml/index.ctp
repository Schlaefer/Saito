<?php

	$xml = Xml::build(array( 'entries' => $entries ), LIBXML_NOEMPTYTAG);
	echo $xml->saveXML();
?>