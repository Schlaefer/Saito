<?php if(!$send) : ?>
	<div id="user_contact" class="user contact">
		<h2><?php echo __('user_contact_title', $this->request->data['User']['username']); ?> </h2>
		<?php echo $this->Form->create(); ?>
		<?php echo $this->Form->label('Message.subject', __('user_contact_subject')); ?>
		<?php echo $this->Form->text('Message.subject'); ?>
		<?php echo $this->Form->label('Message.text', __('user_contact_message')); ?>
		<?php echo $this->Form->textarea('Message.text', array('style' => 'height: 10em')); ?>
		<br />
		<br />
		<?php echo $this->Form->submit(__('Submit'), array('class' => 'btn btn-submit')); ?>
		<?php echo $this->Form->end(); ?>
	</div>
<?php else : ?>
<?php endif; ?>