<?php
	$bookmark_link_set = $this->Html->link(
				'<i id="bookmarks-add-icon-' . $id . '" class="icon-bookmark icon-large"></i>',
				'/bookmarks/index/#' . $id,
				array(
						'id'		 => 'bookmarks-add-' . $id,
						'title'  => __('Entry is bookmarked'),
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
						'title'  => __('Bookmark this entry'),
						'escape' => false,
				)
		);
		echo $this->Html->scriptBlock(<<<EOF
$(document).ready(function (){
	$("#content").one("click", "#bookmarks-add-{$id}", function (event) {
		$.ajax({
			async:true, 
			data:"id={$id}",
			dataType:"json", 
			success:function (data, textStatus) {
				$("#bookmarks-add-{$id}").replaceWith('{$bookmark_link_set}');
				},
			type:"POST", 
			url:"{$this->webroot}bookmarks/add"
		});
		return false;});
	});
EOF
		);
	}

?>