<?php
	$this->start('headerSubnavLeft');
  echo $this->Html->link(
      '<i class="icon-arrow-left"></i> ' . __('back_to_overview_linkname'),
      '/bookmarks/index/#' . $this->request->data['Entry']['id'],
      array( 'class' => 'textlink', 'escape' => FALSE ));
  $this->end();
?>
<div class="box-content">
	<div class="l-box-header box-header">
		<div>
			<div class='c_first_child'></div>
			<div><h1><?php echo __('Edit Bookmark'); ?></h1></div>
			<div class='c_last_child'></div>
		</div>
	</div>
	<div class="content">
		<?php
					$entry = array(
							'Entry'		 => $this->request->data['Entry'],
							'Category' => $this->request->data['Entry']['Category'],
							'User'		 => $this->request->data['Entry']['User'],
					);
			echo $this->element('/entry/view_content', array(
				'entry' => $entry)); ?>
	</div>
	<div class="l-box-footer box-footer-form">
		<?php
			echo $this->Form->create();
			echo $this->Html->div('input textarea',
					$this->Form->textarea('comment', array(
							'maxlength' => 255,
							'columns'   => 80,
							'rows'			=> 3,
			)));
			echo $this->Form->submit(__('btn-comment-title'), array(
					'class' => 'btn btn-submit',
			));
			echo $this->Form->end();
		?>
	</div>
</div>