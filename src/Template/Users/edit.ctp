<?php

  $this->start('headerSubnavLeft');
  echo $this->Layout->navbarBack([
    'controller' => 'users',
    'action' => 'view',
    $user->get('id')
  ]);
  $this->end();

?>
<div class="user edit">
	<?= $this->Form->create($user, ['action' => 'edit']); ?>
	<div class="panel">
		<?= $this->Layout->panelHeading($title_for_page, ['pageHeading' => true]) ?>
    <div class='panel-content panel-form'>
				<?php
					if ($CurrentUser->permission('saito.core.user.edit')) {
						$cells = [
							[
								__('username_marking'),
								$this->Form->input('username', ['label' => false])
							],
							[
								__('userlist_email'),
								$this->Form->input('user_email', array('label' => false))
							],
							[
								__('user_type'),
								$this->Form->radio(
									'user_type',
									[
										'user' => __('user.type.user'),
										'mod' => __('user.type.mod'),
										'admin' => __('user.type.admin'),
									],
									['legend' => false, 'separator' => '<br/>']
								)
							]
						];
					} else {
						$cells = [
							[__('username_marking'), h($user->get('username'))],
							[__('userlist_email'), h($user->get('user_email'))]
						];
					}


					if ($CurrentUser->isSame($user)) {
						$cells[] = [
							__('user_pw'),
							$this->Html->link(
								__('change_password_link'),
								[
									'action' => 'changepassword',
									$user->get('id')
								]
							)
						];
					}

					$cells[] = [
						__('user_real_name'),
						$this->Form->input('user_real_name', ['label' => false]) .
						$this->Html->para('exp', __('user_real_name_exp'))
					];

					$cells[] = [
						__('user_hp'),
						$this->Form->input('user_hp', ['label' => false]) .
						$this->Html->para('exp', __('user_hp_exp'))
					];

					//= place and maps
					$cellContent = $this->Form->input('user_place', ['label' => false]);
					$cellContent .= $this->Html->para('exp', __('user_place_exp'));

					if ($SaitoSettings['map_enabled']) {
						$cellContent .= $this->Map->map(
							$user,
							[
								'type' => 'edit',
								'fields' => [
									'edit' => '#user-place',
									'update' => [
										'lat' => ['#UserUserPlaceLat'],
										'lng' => ['#UserUserPlaceLng'],
										'zoom' => ['#UserUserPlaceZoom']
									]
								],
							]
						);
						$cellContent .= $this->SaitoHelp->icon(5);
						foreach (['lat', 'lng', 'zoom'] as $name) {
							$field = "user_place_$name";
							$cellContent .= $this->Form->hidden(
								$field,
								['id' => 'UserUserPlace' . ucfirst($name), 'label' => false]
							);
							$this->Form->unlockField($field);
							if ($this->Form->isFieldError($field)) {
								$cellContent .= $this->Form->error($field);
							}
						}
					}

					$cells[] = [__('user_place'), $cellContent];

					//= user profile
					$cells[] = [
						__('user_profile'),
						$this->Form->input('profile', ['rows' => '5', 'label' => false,]) .
						$this->Html->para('exp', __('user_profile_exp'))
					];

					$cells[] = [
						__('user_signature'),
						$this->Form->input('signature', ['rows' => '5', 'label' => false]) .
						$this->Html->para('exp', __('user_signature_exp'))
					];

					echo $this->Html->tag(
						'table',
						$this->Html->tableCells($cells),
						['class' => 'table th-left elegant']
					);
				?>
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
				<?php /* @todo 3.0
				<?php  echo $this->Farbtastic->input('User.user_color_new_postings', __('user_color_new_postings_exp')); ?>
				<br/>
				<?php  echo $this->Farbtastic->input('User.user_color_old_postings', __('user_color_old_postinings_exp')); ?>
				<br/>
				<?php  echo $this->Farbtastic->input('User.user_color_actual_posting', __('user_color_actual_posting_exp')); ?>
			</td>
 			*/ ?>
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

			<?php if (!$SaitoSettings['category_chooser_global']
					&& $SaitoSettings['category_chooser_user_override']): ?>
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
		$items = $SaitoEventManager->dispatch(
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
    <?= $this->Form->submit(
        __('gn.btn.save.t'),
        ['id' => 'btn-submit', 'class' => 'btn btn-submit']
    );
    ?>
<?= $this->Form->end(); ?>

    <div class="panel">
        <?= $this->Layout->panelHeading(__('user.set.avatar.t')) ?>
        <div class='panel-content panel-form'>
            <?php
            echo $this->UserH->getAvatar($user);
            echo $this->Form->create($user, ['type' => 'file']);
            echo $this->Form->input(
                'avatar',
                ['type' => 'file', 'required' => false]
            );
            echo $this->Form->button(
                __('gn.btn.save.t'),
                ['class' => 'btn btn-submit']
            );
            $avatar = $user->get('avatar');
            if (!empty($avatar)) {
                echo $this->Form->button(
                    __('gn.btn.delete.t'),
                    [
                        'class' => 'btnLink',
                        'name' => 'avatarDelete',
                        // @todo remove style
                        'style' => 'padding-left: 1em',
                        'value' => '1'
                    ]
                );
            }
            echo $this->Form->end();
            ?>
        </div>
    </div>


</div>
