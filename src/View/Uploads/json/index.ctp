<?php
	$out = array();
	foreach ($images as $image) {
		$current = array(
			'id' => $image['Upload']['id'],
			'name' => $image['Upload']['name'],
		);
		$current['linkDelete'] = $this->Html->link(
			'<i class="fa fa-trash-o" title="' .
			__('upload_btn_delete_upload') . '"></i>',
			'#',
			array(
				'escape' => false,
				'title' => __('upload_btn_delete_upload'),
			)
		);

		$current['linkImage'] = $this->Html->link(
			$this->FileUpload->image(
				$image['Upload']['name'],
				array('class' => 'upload_box_image', 'imagePathOnly' => false)
			),
			$this->FileUpload->image(
				$image['Upload']['name'],
				array('imagePathOnly' => true)
			),
			array(
				'escape' => false,
				'target' => '_blank',
			)
		);

		$out[] = $current;

	}
	echo json_encode($out);