<?php
  $this->start('headerSubnavLeft');
  echo $this->Layout->navbarBack([
    'controller' => 'users',
    'action' => 'view',
    $userId
  ]);
  $this->end();
?>
<div class="user edit">
  <?php echo $this->Form->create('User', ['url' => ['controller' => 'users', 'action' => 'edit']] ); ?>
	<div class="panel">
		<?= $this->Layout->panelHeading($title_for_page,
				['pageHeading' => true]) ?>
    <div class='panel-content panel-form'>
			<table class="table th-left elegant">

				<?php if ($CurrentUser->isAdmin()): ?>
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
						<td>
							<?php
									echo $this->Form->radio('user_type',
											[
													'user'	 => __('user.type.user'),
													'mod'		 => __('user.type.mod'),
													'admin'	 => __('user.type.admin'),
											],
											['legend' => false, 'separator' => '<br/>']
									);
									?>
						</td>
					</tr>
      <?php else: ?>
				<tr>
					<td> <?php echo __('username_marking'); ?></td>
					<td> <?= h($this->request->data['User']['username']) ?> </td>
				</tr>

				<tr>
					<td> <?php echo __('userlist_email'); ?> </td>
					<td> <?= h($this->request->data['User']['user_email']) ?> </td>
				</tr>
			<?php endif; ?>


      <?php
        if ($CurrentUser->isSame($this->request->data)) { ?>
        <tr>
          <td> <?php echo __('user_pw') ?> </td>
          <td>
            <?=
              $this->Html->link(__('change_password_link'), [
                'action' => 'changepassword',
                $userId
              ])
            ?>
          </td>
        </tr>
      <?php } ?>

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
				<td>
					<?php
						echo $this->Form->input('user_place', ['label' => false]);
						echo $this->Html->para('exp', __('user_place_exp'));

						if (Configure::read('Saito.Settings.map_enabled')):
							echo $this->Map->map($this->request->data,
								[
									'type' => 'edit',
									'fields' => [
										'edit' => '#UserUserPlace',
										'update' => [
											'lat' => ['#UserUserPlaceLat'],
											'lng' => ['#UserUserPlaceLng'],
											'zoom' => ['#UserUserPlaceZoom']
										]
									],
								]);
							echo $this->SaitoHelp->icon(5);
							foreach (['lat', 'lng', 'zoom'] as $name) {
								$field = "user_place_$name";
								echo $this->Form->hidden($field, ['label' => false]);
								$this->Form->unlockField('User.' . $field);
								if ($this->Form->isFieldError($field)) {
									echo $this->Form->error($field);
								}
							}
						endif;
					?>
				</td>
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

			</table>
		</div>
  </div>

	<div class="panel">
	<?= $this->Layout->panelHeading(__('Settings')) ?>
	<div class='panel-content panel-form'>
	<table class="table th-left elegant">

		<tr>
			<td> <?php echo __('user_sort_last_answer') ?> </td>
			<td>
				<?php
					echo $this->Form->radio(
							'user_sort_last_answer',
							array(
									'0'	 => __('user_sort_last_answer_time', 1),
									'1'	 => __('user_sort_last_answer_last_answer', 1)
							),
							array(
									'legend'		 => false,
									'separator'	 => '<br/>',
							)
					);
				?>
				<p class="exp"> <?php echo __('user_sort_last_answer_exp') ?> </p>
			</td>
		</tr>

		<tr>
			<td> <?php echo __('user_automaticaly_mark_as_read') ?> </td>
			<td>
				<?= $this->Form->checkbox('user_automaticaly_mark_as_read', ['label' => false ]); ?>
				<p class="exp">
					<?php
						echo __('user_automaticaly_mark_as_read_exp');
						echo '&nbsp;';
						echo $this->SaitoHelp->icon(2);
					?>
				</p>
			</td>
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
				<?php
					echo $this->Form->input(
						'user_forum_refresh_time',
						[
								'maxLength' => 3,
								'label' => false,
								'min' => 0,
								'max' => 999,
								]
						);
					echo $this->Html->para('exp', __('user_forum_refresh_time_exp'))
				?>
			</td>
		</tr>

		<?php if (count($availableThemes) > 1): ?>
			<tr>
				<td> <?= __('user_theme') ?> </td>
				<td> <?=
						$this->Form->input('user_theme',
								[
										'options' => $availableThemes,
										'label' => false,
								]) ?>
					<p class="exp"> <?= __('user_theme_exp') ?> </p>
				</td>
			</tr>
		<?php endif; ?>

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

		<tr>
			<td> <?php echo __('inline_view_on_click') ?> </td>
			<td>
					<?php echo  $this->Form->checkbox('inline_view_on_click'); ?>
					<p class="exp"> <?php echo __('inline_view_on_click_exp') ?> </p>
			</td>
		</tr>
		<tr>
			<td> <?php echo __('user_show_thread_collapsed') ?> </td>
			<td>
					<?php echo  $this->Form->checkbox('user_show_thread_collapsed'); ?>
					<p class="exp"> <?php echo __('user_show_thread_collapsed_exp') ?> </p>
			</td>
		</tr>

			<tr>
				<td> <?php echo __('user_pers_msg') ?> </td>
				<td> <?php echo  $this->Form->checkbox('personal_messages'); ?> <p class="exp"> <?php echo __('user_pers_msg_exp') ?> </p></td>
			</tr>

			<?php if (!Configure::read('Saito.Settings.category_chooser_global')
					&& Configure::read('Saito.Settings.category_chooser_user_override')): ?>
			<tr>
				<td>
					<?php echo __('user_category_override') ?>
				</td>
				<td>
					<?php echo $this->Form->checkbox('user_category_override'); ?>
					<p class="exp">
						<?php echo __('user_category_override_exp') ?>
					</p>
				</td>
			</tr>
			<?php endif; ?>
	</table>
  </div> <!-- content -->
  </div>

	<?php
		//= get additional profile info from plugins
		$items = SaitoEventManager::getInstance()->dispatch(
			'Request.Saito.View.User.edit',
			[
				'user' => $user,
				'View' => $this
			]
		);
		if ($items) {
			foreach ($items as $item) {
				echo $item;
			}
		}
	?>

	<br	/>
	<?php echo $this->Form->submit(__("button_save"), array ( 'id' => 'btn-submit', 'class' => 'btn btn-submit' )); ?>
<?php echo $this->Form->end(); ?>
</div>
