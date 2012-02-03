<?php if(!$send) : ?>
	<div id="user_contact" class="user contact">
		<h2><?php echo __('user_contact_title'); ?> <?php echo $this->data['User']['username']; ?></h2>
		<?php echo $form->create(); ?>
		<?php echo $form->label('Message.subject', __('user_contact_subject', true)); ?>
		<?php echo $form->text('Message.subject'); ?>
		<?php echo $form->label('Message.text', __('user_contact_message', true)); ?>
		<?php echo $form->textarea('Message.text', array('style' => 'height: 10em')); ?>
		<br />
		<br />
		<?php echo $form->submit(__('Submit', true), array('class' => 'btn_submit')); ?>
		<?php echo $form->end(); ?>
	</div>
<?php else : ?>
<?php endif; ?>