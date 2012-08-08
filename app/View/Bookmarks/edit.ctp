<div class="box-content">
	<div class="l-box-header box-header">
		<div>
			<div class='c_first_child'></div>
			<div><h1><?php echo __('Edit Bookmark'); ?></h1></div>
			<div class='c_last_child'></div>
		</div>
	</div>
	<div class="content">
		<?php echo $this->request->data['Entry']['subject']; ?>
		<?php
			echo $this->Form->create();
			echo $this->Form->textarea('comment');
			echo $this->Form->submit();
			echo $this->Form->end();
		?>
	</div>
</div>