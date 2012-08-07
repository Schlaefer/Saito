<?php
	if (isset($isBookmarked) && $isBookmarked) {
		echo $this->Html->link(
				'<i id="bookmarks-add-icon-' . $id . '" class="icon-bookmark icon-large"></i>', '#',
				array(
				'id'		 => 'bookmarks-add-' . $id,
				'escape' => false,
				)
		);
	} else {
		echo $this->Html->link(
				'<i id="bookmarks-add-icon-' . $id . '" class="icon-bookmark-empty icon-large"></i>', '#',
				array(
				'id'		 => 'bookmarks-add-' . $id,
				'escape' => false,
				)
		);
		$this->Js->get('#bookmarks-add-' . $id);
		echo $this->Js->event(
				'click',
				$this->Js->request(
						array('controller' => 'bookmarks', 'action'		 => 'add'),
						array(
						'async'	 => true,
						'data'	 => array('id'	 => $id),
						'method' => 'POST',
						'success' => '$("#bookmarks-add-icon-'.$id.'").removeClass("icon-bookmark-empty").addClass("icon-bookmark");',
						'type'	 => 'json',
						)
				)
		);
	}

?>