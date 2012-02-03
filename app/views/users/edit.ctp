<h1><?php __('user_area_title'); ?></h1>
<div id="user_edit" class="user edit">
<h2> <?php echo $this->data['User']['username']; ?>: <?php echo __('edit_userdata'); ?> </h2>

<?php echo $form->create('User', array( 'action' => 'edit' ) ); ?>
	<table class="table_th_left_1">

		<? if ( $CurrentUser->isAdmin() ) : ?>

			<tr>
				<td> <?php echo __('username_marking'); ?></td>
				<td> <?php echo  $form->input('username', array( 'label' => false ));  ?> </td>
			</tr>

			<tr>
				<td> <?php echo __('userlist_email'); ?></td>
				<td> <?php echo  $form->input('user_email', array( 'label' => false )); ?> </td>
			</tr>

			<tr>
				<td> <?php echo __('user_type'); ?></td>
				<td> <?php echo $form->radio('user_type', array( 'user' => '@lo User', 'mod' => '@lo Mod' , 'admin' => '@lo Admin'), array( 'legend' => false));  ?> </td>
			</tr>

			<tr>
				<td> <?php echo __('user_pw') ?> </td>
				<td> @td änderungsmöglichkeit für admin (?) </td>
			</tr>

		<? else : ?>

			<tr>
				<td> <?php echo __('username_marking'); ?></td>
				<td> <?php echo $this->data['User']['username']; ?> </td>
			</tr>

			<tr>
				<td> <?php echo __('userlist_email'); ?> </td>
				<td> <?php echo $this->data['User']['user_email']; ?> </td>
			</tr>

			<tr>
				<td> <?php echo __('user_pw') ?> </td>
				<td>
						<?php echo $html->link(
									__("change_password_link", true),
									array ( 'action' => 'changepassword', $this->data['User']['id'] )
								)
								?>
						<p class="exp"> <?php echo __('user_pw_exp') ?> </p>
				</td>
			</tr>

		<? endif ; ?>

		<!-- currently not supported in Saito
		<tr>
			<td> <?php echo __('user_show_email'); ?></td>
			<td>
				<?php echo  $form->checkbox('hide_email'); ?>
				<p class="exp"> <?php echo __('user_show_email_exp') ?> </p>
			</td>
		</tr>
		-->

		<tr>
			<td> <?php echo __('user_real_name'); ?></td>
			<td> <?php echo  $form->input('user_real_name', array( 'label' => false ));  ?>
				<p class="exp"> <?php echo __('user_real_name_exp') ?> </p>
			</td>
		</tr>

		<tr>
			<td> <?php echo __('user_hp'); ?></td>
			<td> <?php echo  $form->input('user_hp', array( 'label' => false ));  ?>
				<p class="exp"> <?php echo __('user_hp_exp') ?> </p>
			</td>
		</tr>

		<tr>
			<td> <?php echo __('user_place') ?></td>
			<td> <?php echo  $form->input('user_place', array( 'label' => false ));  ?> <p class="exp"> <?php echo __('user_place_exp') ?> </p></td>
		</tr>

		<tr>
			<td> <?php echo __('user_profile') ?> </td>
			<td> <?php echo  $form->input('profile', array(
					'rows'	=> '5',
					'label'	=> false,
			));  ?>
			<p class="exp"> <?php echo __('user_profile_exp') ?> </p>
			</td>
		</tr>


		<tr>
			<td> <?php echo __('user_signature') ?> </td>
			<td> <?php echo  $form->input('signature', array(
					'rows'	=> 5,
					'label'	=> false,
			));  ?>
			<p class="exp"> <?php echo __('user_signature_exp') ?> </p>
			</td>
		</tr>


		<tr>
			<td> <?php echo __('user_font_size') ?> </td>
			<td> <?php echo  $form->input('user_font_size', array(
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
			<td> <?php echo  $form->checkbox('user_automaticaly_mark_as_read', array( 'label' => false ));  ?> <p class="exp"> <?php echo __('user_automaticaly_mark_as_read_exp') ?> </p></td>
		</tr>

		<tr>
			<td> <?php echo __('user_signatures_hide') ?> </td>
			<td>
				<?php echo  $form->checkbox('user_signatures_hide');  ?> <p class="exp"> <?php echo __('user_signatures_hide_exp') ?> </p>
				<br/>
				<?php echo  $form->checkbox('user_signatures_images_hide'); ?> <p class="exp"> <?php echo __('user_signatures_images_hide_exp') ?> </p>
			</td>
		</tr>

		<tr>
			<td> <?php echo __('user_forum_refresh_time') ?> </td>
			<td> 
				<?php echo $form->input('user_forum_refresh_time', array( 'maxLength' => 3 , 'label'=>false, 'style' => 'width: 3em;')); ?>
				<p class="exp">
					<?php echo __('user_forum_refresh_time_exp') ?>
				</p>
			</td>
		</tr>

		<tr>
			<td> <?php echo __('user_colors') ?> </td>
			<td>
				<? echo $farbtastic->input('User.user_color_new_postings', __('user_color_new_postings_exp', true)); ?>
				<br/>
				<? echo $farbtastic->input('User.user_color_old_postings', __('user_color_old_postinings_exp', true)); ?>
				<br/>
				<? echo $farbtastic->input('User.user_color_actual_posting', __('user_color_actual_posting_exp', true)); ?>
			</td>
		</tr>

		<!-- currently not supported in Saito
		<tr>
			<td> <?php echo __('user_forum_hr_ruler') ?> </td>
			<td> <?php echo  $form->checkbox('user_forum_hr_ruler'); ?> <p class="exp"> <?php echo __('user_forum_hr_exp') ?> </p></td>
		</tr>
		-->

		<!-- currently not supported in Saito
		<tr>
			<td> <?php echo __('user_standard_view') ?> </td>
			<td> <?php echo $form->radio('user_view', array( 'thread' => __('user_view_thread', true), 'board' => __('user_view_board', 1) , 'mix' => __('user_view_mixed', 1)), array( 'legend' => false));  ?>
				<p class="exp"> <?php echo __('user_standard_view_exp') ?> </p>
			</td>
		</tr>
		-->
		<tr>
			<td> <?php echo __('inline_view_on_click') ?> </td>
			<td>
					<?php echo  $form->checkbox('inline_view_on_click'); ?>
					<p class="exp"> <?php echo __('inline_view_on_click_exp') ?> </p>
			</td>
		</tr>

		<tr>
			<td> <?php echo __('user_sort_last_answer') ?> </td>
			<td>
				<?php echo $form->radio('user_sort_last_answer', array( '0' => __('user_sort_last_answer_time', 1), '1' => __('user_sort_last_answer_last_answer', 1)), array( 'legend' => false));  ?>
				<p class="exp"> <?php echo __('user_sort_last_answer_exp') ?> </p>
			</td>
		</tr>



			<tr>
				<td> <?php echo __('user_pers_msg') ?> </td>
				<td> <?php echo  $form->checkbox('personal_messages'); ?> <p class="exp"> <?php echo __('user_pers_msg_exp') ?> </p></td>
			</tr>

		<? if(false) : ?>
			<tr>
				<td> <?php echo __('user_standard_categories') ?> </td>
				<td>
					@td Kateogrie Handling
					<p class="exp"> <?php echo __('user_standard_categories_exp') ?> </p>
				</td>
			</tr>
			<tr>
				<td> <?php echo __('user_time_diff') ?> </td>
				<td> <?php echo $form->input('time_difference', array( 'options' => array_combine(range(-24,24),range(-24,24)), 'label' => false,));  ?> <p class="exp"> <?php echo __('user_time_diff_exp') ?> </p></td>
			</tr>

			<? if ( $CurrentUser->isMod() ) : ?>
				<tr>
					<td> <?php echo __('admin_mod_notif') ?> </td>
					<td>
						<p class="exp"> <?php echo __('admin_mod_notif_exp') ?> </p>
						<?php echo  $form->checkbox('new_posting_notify');  ?> <p class="exp"> <?php echo __('new_posting_notify_exp') ?> </p>
						<br/>
						<?php echo  $form->checkbox('new_user_notify'); ?> <p class="exp"> <?php echo __('new_user_notify_exp') ?> </p>
					</td>
				</tr>
			<? endif ; ?>
		<? endif; ?>
	</table>
	<br	/>

	<h2>  <?php echo __('flattr'); ?> </h2>
	<table class="table_th_left_1">
		<tr>
			<td><?php echo __('flattr_uid'); ?></td>
			<td><?php echo $form->input('flattr_uid', array( 'label' => false )); ?> </td>
		</tr>
		<tr>
			<td><?php echo __('flattr_allow_user'); ?></td>
			<td><?php echo $form->checkbox('flattr_allow_user', array ( 'label' => false )); ?> <p class="exp"> <?php echo __('flattr_allow_user_exp') ?> </p> </td>
		</tr>
		<tr>
			<td><?php echo __('flattr_allow_posting'); ?></td>
			<td><?php echo $form->checkbox('flattr_allow_posting', array ( 'label' => false )); ?> <p class="exp"> <?php echo __('flattr_allow_posting_exp') ?> </p> </td>
		</tr>
	</table>
	<br	/>
	<?php echo $form->submit(__("button_save",true), array ( 'id' => 'btn_submit', 'class' => 'btn_submit' )); ?>
<?php echo $form->end(); ?>
</div>