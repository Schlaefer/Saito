<?php
	$this->set('documentData', array('xmlns:dc' => 'http://purl.org/dc/elements/1.1/'));

	$this->set('channelData', array(
			'title' => Configure::read('Saito.Settings.forum_name') . ' – ' . $title,
			'link' => $this->Html->url('/', true),
			# 'description' => __("Most recent posts."),
			'language' => 'de-de'));

		foreach ($entries as $entry) {
			$postTime = strtotime($entry['Entry']['time']);

			$postLink = array(
					'controller' => 'entries',
					'action' => 'view',
					$entry['Entry']['id'],
					);

			$bodyText = '';
			$bodyText = $this->Bbcode->parse($entry['Entry']['text']);
			/* 
			// You should import Sanitize
			App::import('Sanitize');
			// This is the part where we clean the body text for output as the description 
			// of the rss item, this needs to have only text to make sure the feed validates
			$bodyText = preg_replace('=\(.*?\)=is', '', $entry['Entry']['text']);
			$bodyText = $this->Text->stripLinks($bodyText);
			$bodyText = Sanitize::stripAll($bodyText);
			$bodyText = $this->Text->truncate($bodyText, 400, array(
					'ending' => '...',
					'exact'  => true,
					'html'   => true,
			));
			 * 
			 */

			echo  $this->Rss->item(array(), array(
					'title' 			=> html_entity_decode($entry['Entry']['subject'], ENT_NOQUOTES,'UTF-8'),
					'link' 				=> $postLink,
					'guid' 				=> array('url' => $postLink, 'isPermaLink' => 'true'),
					'description' => array('value' => $bodyText),
					'dc:creator' 	=> $entry['Entry']['name'],
					'pubDate' 		=> $entry['Entry']['time'],
					)
				);
	}
?>
