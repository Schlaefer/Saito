<?php

	echo $this->Html->link(
			'<i class="icon-bookmark-empty icon-large"></i>', 
			'#',
			array(
					'id'		 => 'bookmarks-subscribe-' . $id,
					'escape' => false,
			)
	);

	$this->Js->get('#bookmarks-subscribe-' . $id);
	echo $this->Js->event(
			'click',
			$this->Js->request(
					array('controller' => 'bookmarks', 'action'		 => 'subscribe', $id),
					array('async'	 => true, 'update' => '#element')
			)
	);
?>