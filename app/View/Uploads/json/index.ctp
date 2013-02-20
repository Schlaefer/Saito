<?php
	$out = array();
	foreach ($images as $image) {
		$current = array(
			'id' => $image['Upload']['id']
		);
		$current['linkDelete'] = $this->Html->link(
			$this->Html->image(
				'close_db.png',
				array('alt' => __('upload_btn_delete_upload'))
			),
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

		$current['linkInsert'] = $this->Html->link(
			__('upload_btn_insert_into_posting'),
			'#',
			array(
				'class' => 'btn btn-submit',
			)
		);

		$out[] = $current;

	}
	echo json_encode($out);