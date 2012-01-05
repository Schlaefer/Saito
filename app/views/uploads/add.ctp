<div style="display: table; width: 100%;">
	<?php echo $form->create('Upload', array('action' => 'add', 'type' => 'file', 'inputDefaults' => array('div' => false, 'label' => false))); ?>
	<div class="left cell">
		<?php echo $fileUpload->input(array('style'=>'width: 100%;')); ?>
		<br/>
		(max. <?php echo Configure::read('Saito.Settings.upload_max_img_size'); ?> kB; jpg, jpeg, png, gif)
	</div>
	<div class="right cell">
		<?php echo $form->create('Upload', array('action' => 'add', 'type' => 'file', 'inputDefaults' => array('div' => false, 'label' => false))); ?>
		<?php echo $form->submit(__("upload_btn", true), array('class' => 'btn_submit')); ?>
	</div>
	<?php echo $form->end(); ?>
</div>