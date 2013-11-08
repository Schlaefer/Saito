<?php
  $this->start('headerSubnavLeft');
  echo $this->Html->link(
      '<i class="fa fa-arrow-left"></i> ' . __('Back'),
      array ( 'controller' => 'users', 'action' => 'edit', $this->request->data['User']['id'] ),
      array( 'class' => 'textlink', 'escape' => FALSE ));
  $this->end();
?>
<h1><?php echo __('change_password_link').': '.$this->request->data['User']['username']; ?></h1>
<div class="box_layout_1 box-form">
	<?php
	 echo $this->Form->create('User');
	 echo $this->Form->input('password_old', array(
			 'type'=>'password',
			 'label'	=> __('change_password_old_password'),
			 'div' => array( 'class'	=> 'input password required' ),
			 'error' => array (
					 'notEmpty'	=> __('error_password_empty'),
					 'pwCheckOld'	=> __('error_password_check_old'),
			 )));
	 echo $this->Form->input('user_password', array(
			 'type'=>'password',
			 'label'	=> __('change_password_new_password'),
			 'div'		=> array( 'class' => 'required' ),
			 'error' => array (
					 'notEmpty'	=> __('error_password_empty'),
					 'pwConfirm'	=> __('error_password_confirm'),
			 )));
	 echo $this->Form->input('password_confirm', array(
			 'type'=>'password',
			 'div'		=> array( 'class' => 'required' ),
			 'label'	=> __('change_password_new_password_confirm'),
			 ));
	 echo $this->Form->submit(__('change_password_btn_submit'), array( 'class'=> 'btn btn-submit'));
	 echo $this->Form->end();
	?>
</div>
