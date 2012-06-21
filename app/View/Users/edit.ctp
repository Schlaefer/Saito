<?php
  $this->start('headerSubnavLeft');
  echo $this->Html->link(
      '<i class="icon-arrow-left"></i> ' . __('Back'),
      array ( 'controller' => 'users', 'action' => 'view', $this->request->data['User']['id']),
      array( 'class' => 'textlink', 'escape' => FALSE ));
  $this->end();
?>
<h1><?php echo __('user_area_title'); ?></h1>
<div id="user_edit" class="user edit">
  <?php echo $this->Form->create('User', array( 'action' => 'edit' ) ); ?>
	<div class="box-form">
		<div class="l-box-header box-header">
			<div>
				<div class='c_first_child'></div>
				<div>
          <h2>
            <?php echo $this->request->data['User']['username']; ?>: <?php echo __('edit_userdata'); ?>
          </h2>
        </div>
				<div class='c_last_child'></div>
			</div>
		</div>
    <div class='content'>
	<table class="table th-left elegant">

		<?php  if ( $CurrentUser->isAdmin() ) : ?>

			<tr>
				<td> <?php echo __('username_marking'); ?></td>
				<td> <?php echo  $this->Form->input('username', array( 'label' => false ));  ?> </td>
			</tr>

			<tr>
				<td> <?php echo __('userlist_email'); ?></td>
				<td> <?php echo  $this->Form->input('user_email', array( 'label' => false )); ?> </td>
			</tr>

			<tr>
				<td> <?php echo __('user_type'); ?></td>
				<td> <?php echo $this->Form->radio('user_type', array( 'user' => '@lo User', 'mod' => '@lo Mod' , 'admin' => '@lo Admin'), array( 'legend' => false));  ?> </td>
			</tr>

      <?php  if ( $CurrentUser->getId() == $this->request->data['User']['id'] ) : ?>
        <tr>
          <td> <?php echo __('user_pw') ?> </td>
          <td>
              <?php echo $this->Html->link(
                    __("change_password_link"),
                    array ( 'action' => 'changepassword', $this->request->data['User']['id'] )
                  )
                  ?>
              <p class="exp"> <?php echo __('user_pw_exp') ?> </p>
          </td>
        </tr>
        <?php  else : ?>
        <tr>
          <td> <?php echo __('user_pw') ?> </td>
          <td> @td änderungsmöglichkeit für admin (?) </td>
        </tr>
      <?php  endif ; ?>

		<?php  else : ?>

			<tr>
				<td> <?php echo __('username_marking'); ?></td>
				<td> <?php echo $this->request->data['User']['username']; ?> </td>
			</tr>

			<tr>
				<td> <?php echo __('userlist_email'); ?> </td>
				<td> <?php echo $this->request->data['User']['user_email']; ?> </td>
			</tr>

			<tr>
				<td> <?php echo __('user_pw') ?> </td>
				<td>
						<?php echo $this->Html->link(
									__("change_password_link"),
									array ( 'action' => 'changepassword', $this->request->data['User']['id'] )
								)
								?>
						<p class="exp"> <?php echo __('user_pw_exp') ?> </p>
				</td>
			</tr>

		<?php  endif ; ?>

		<!-- currently not supported in Saito
		<tr>
			<td> <?php echo __('user_show_email'); ?></td>
			<td>
				<?php echo  $this->Form->checkbox('hide_email'); ?>
				<p class="exp"> <?php echo __('user_show_email_exp') ?> </p>
			</td>
		</tr>
		-->

		<tr>
			<td> <?php echo __('user_real_name'); ?></td>
			<td> <?php echo  $this->Form->input('user_real_name', array( 'label' => false ));  ?>
				<p class="exp"> <?php echo __('user_real_name_exp') ?> </p>
			</td>
		</tr>

		<tr>
			<td> <?php echo __('user_hp'); ?></td>
			<td> <?php echo  $this->Form->input('user_hp', array( 'label' => false ));  ?>
				<p class="exp"> <?php echo __('user_hp_exp') ?> </p>
			</td>
		</tr>

		<tr>
			<td> <?php echo __('user_place') ?></td>
			<td> <?php echo  $this->Form->input('user_place', array( 'label' => false ));  ?> <p class="exp"> <?php echo __('user_place_exp') ?> </p></td>
		</tr>

		<tr>
			<td> <?php echo __('user_profile') ?> </td>
			<td> <?php echo  $this->Form->input('profile', array(
					'rows'	=> '5',
					'label'	=> false,
			));  ?>
			<p class="exp"> <?php echo __('user_profile_exp') ?> </p>
			</td>
		</tr>


		<tr>
			<td> <?php echo __('user_signature') ?> </td>
			<td> <?php echo  $this->Form->input('signature', array(
					'rows'	=> 5,
					'label'	=> false,
			));  ?>
			<p class="exp"> <?php echo __('user_signature_exp') ?> </p>
			</td>
		</tr>

		<tr>
			<td> <?php echo __('user_font_size') ?> </td>
			<td> <?php echo  $this->Form->input('user_font_size', array(
					'options' => array (
						'0.75'	=> '-5',
						'0.8'		=> '-4',
						'0.85'	=> '-3',
						'0.9'		=> '-2',
						'0.95'	=> '-1',
						'1'			=> '0',
						'1.05'	=> '1',
						'1.10'	=> '2',
						'1.15'	=> '3',
						'1.20'	=> '4',
						'1.25'	=> '5',
					),
					'label'	=> false,
					));  ?>
				<p class="exp"> <?php echo __('user_font_size_exp') ?> </p>
			</td>
		</tr>

		<tr>
			<td> <?php echo __('user_automaticaly_mark_as_read') ?> </td>
			<td> <?php echo  $this->Form->checkbox('user_automaticaly_mark_as_read', array( 'label' => false ));  ?> <p class="exp"> <?php echo __('user_automaticaly_mark_as_read_exp') ?> </p></td>
		</tr>

		<tr>
			<td> <?php echo __('user_signatures_hide') ?> </td>
			<td>
				<?php echo  $this->Form->checkbox('user_signatures_hide');  ?> <p class="exp"> <?php echo __('user_signatures_hide_exp') ?> </p>
				<br/>
				<?php echo  $this->Form->checkbox('user_signatures_images_hide'); ?> <p class="exp"> <?php echo __('user_signatures_images_hide_exp') ?> </p>
			</td>
		</tr>

		<tr>
			<td> <?php echo __('user_forum_refresh_time') ?> </td>
			<td> 
				<?php echo $this->Form->input('user_forum_refresh_time', array( 'maxLength' => 3 , 'label'=>false, 'style' => 'width: 3em;')); ?>
				<p class="exp">
					<?php echo __('user_forum_refresh_time_exp') ?>
				</p>
			</td>
		</tr>

		<tr>
			<td> <?php echo __('user_colors') ?> </td>
			<td>
				<?php  echo $this->Farbtastic->input('User.user_color_new_postings', __('user_color_new_postings_exp')); ?>
				<br/>
				<?php  echo $this->Farbtastic->input('User.user_color_old_postings', __('user_color_old_postinings_exp')); ?>
				<br/>
				<?php  echo $this->Farbtastic->input('User.user_color_actual_posting', __('user_color_actual_posting_exp')); ?>
			</td>
		</tr>

		<!-- currently not supported in Saito
		<tr>
			<td> <?php echo __('user_forum_hr_ruler') ?> </td>
			<td> <?php echo  $this->Form->checkbox('user_forum_hr_ruler'); ?> <p class="exp"> <?php echo __('user_forum_hr_exp') ?> </p></td>
		</tr>
		-->

		<!-- currently not supported in Saito
		<tr>
			<td> <?php echo __('user_standard_view') ?> </td>
			<td> <?php echo $this->Form->radio('user_view', array( 'thread' => __('user_view_thread'), 'board' => __('user_view_board', 1) , 'mix' => __('user_view_mixed', 1)), array( 'legend' => false));  ?>
				<p class="exp"> <?php echo __('user_standard_view_exp') ?> </p>
			</td>
		</tr>
		-->
		<tr>
			<td> <?php echo __('inline_view_on_click') ?> </td>
			<td>
					<?php echo  $this->Form->checkbox('inline_view_on_click'); ?>
					<p class="exp"> <?php echo __('inline_view_on_click_exp') ?> </p>
			</td>
		</tr>

		<tr>
			<td> <?php echo __('user_sort_last_answer') ?> </td>
			<td>
				<?php echo $this->Form->radio('user_sort_last_answer', array( '0' => __('user_sort_last_answer_time', 1), '1' => __('user_sort_last_answer_last_answer', 1)), array( 'legend' => false));  ?>
				<p class="exp"> <?php echo __('user_sort_last_answer_exp') ?> </p>
			</td>
		</tr>



			<tr>
				<td> <?php echo __('user_pers_msg') ?> </td>
				<td> <?php echo  $this->Form->checkbox('personal_messages'); ?> <p class="exp"> <?php echo __('user_pers_msg_exp') ?> </p></td>
			</tr>

		<?php  if(false) : ?>
			<tr>
				<td> <?php echo __('user_standard_categories') ?> </td>
				<td>
					@td Kateogrie Handling
					<p class="exp"> <?php echo __('user_standard_categories_exp') ?> </p>
				</td>
			</tr>
			<tr>
				<td> <?php echo __('user_time_diff') ?> </td>
				<td> <?php echo $this->Form->input('time_difference', array( 'options' => array_combine(range(-24,24),range(-24,24)), 'label' => false,));  ?> <p class="exp"> <?php echo __('user_time_diff_exp') ?> </p></td>
			</tr>

			<?php  if ( $CurrentUser->isMod() ) : ?>
				<tr>
					<td> <?php echo __('admin_mod_notif') ?> </td>
					<td>
						<p class="exp"> <?php echo __('admin_mod_notif_exp') ?> </p>
						<?php echo  $this->Form->checkbox('new_posting_notify');  ?> <p class="exp"> <?php echo __('new_posting_notify_exp') ?> </p>
						<br/>
						<?php echo  $this->Form->checkbox('new_user_notify'); ?> <p class="exp"> <?php echo __('new_user_notify_exp') ?> </p>
					</td>
				</tr>
			<?php  endif ; ?>
		<?php  endif; ?>
	</table>
  </div>
  </div>
	<br	/>

	<div class="box-form">
		<div class="l-box-header box-header">
			<div>
				<div class='c_first_child'></div>
				<div>
          <h2>
            <?php echo __('flattr'); ?>
          </h2>
        </div>
				<div class='c_last_child'></div>
			</div>
		</div>
  <div class='content'>
    <table class="table th-left elegant">
      <tr>
        <td><?php echo __('flattr_uid'); ?></td>
        <td><?php echo $this->Form->input('flattr_uid', array( 'label' => false )); ?> </td>
      </tr>
      <tr>
        <td><?php echo __('flattr_allow_user'); ?></td>
        <td><?php echo $this->Form->checkbox('flattr_allow_user', array ( 'label' => false )); ?> <p class="exp"> <?php echo __('flattr_allow_user_exp') ?> </p> </td>
      </tr>
      <tr>
        <td><?php echo __('flattr_allow_posting'); ?></td>
        <td><?php echo $this->Form->checkbox('flattr_allow_posting', array ( 'label' => false )); ?> <p class="exp"> <?php echo __('flattr_allow_posting_exp') ?> </p> </td>
      </tr>
    </table>
  </div>
  </div>
	<br	/>
	<?php echo $this->Form->submit(__("button_save"), array ( 'id' => 'btn-submit', 'class' => 'btn btn-submit' )); ?>
<?php echo $this->Form->end(); ?>
</div>