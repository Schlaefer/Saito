<?php
	$bookmark_link_set = $this->Html->link(
				'<i id="bookmarks-add-icon-' . $id . '" class="icon-bookmark icon-large"></i>',
				'/bookmarks/index/#' . $id,
				array(
				'id'		 => 'bookmarks-add-' . $id,
				'escape' => false,
				)
		);

	if (isset($isBookmarked) && $isBookmarked) {
		echo $bookmark_link_set;
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
						'success' => '$("#bookmarks-add-'.$id.'").replaceWith(\''.$bookmark_link_set.'\');',
						'type'	 => 'json',
						)
				)
		);
	}

?>