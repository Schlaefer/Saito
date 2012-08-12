<?php if(!$send) : ?>
	<div id="user_contact" class="user contact">
		<div class="box-form">
			<div class="l-box-header box-header">
				<div>
					<div class='c_first_child'></div>
					<div><h2><?php echo __('user_contact_title', $this->request->data['User']['username']); ?> </h2></div>
					<div class='c_last_child'></div>
				</div>
			</div>
			<div class="content">
				<?php echo $this->Form->create(); ?>
				<?php echo $this->Form->label('Message.subject', __('user_contact_subject')); ?>
				<?php echo $this->Form->text('Message.subject'); ?>
				<?php echo $this->Form->label('Message.text', __('user_contact_message')); ?>
				<?php echo $this->Form->textarea('Message.text', array('style' => 'height: 10em')); ?>
				<br/>
				<?php echo $this->Form->submit(__('Submit'), array(
						'class' => 'btn btn-submit'
						)); ?>
				<?php echo $this->Form->end(); ?>
			</div>
		</div>
	</div>
<?php else : ?>
<?php endif; ?>