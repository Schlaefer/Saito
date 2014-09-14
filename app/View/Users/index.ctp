<?php
	$this->start('headerSubnavLeft');
	echo $this->Layout->navbarBack();
	$this->end();

	$this->element('users/menu');
?>
<div class="user index">

	<div class="panel">
		<?= $this->Layout->panelHeading($title_for_page, ['pageHeading' => true]) ?>
		<div class="panel-content">
			<div class="table-menu sort-menu">
				<?php
          foreach ($menuItems as $title => $mi) {
            $menu[] = $this->Paginator->sort($title, $mi[0], $mi[1]);
          }
					echo __('Sort by: %s', implode(', ', $menu));
				?>
			</div>
			<table class="table th-left row-sep">
				<tbody>
				<?php
					foreach ($users as $user): ?>
						<tr>
							<td>
								<?=
									$this->Html->link(
											$user['User']['username'],
											'/users/view/' . $user['User']['id']);
								?>
							</td>
							<td>
								<?php
									$_u = [
											$this->UserH->type($user['User']['user_type']),
											__('user_since %s',
													$this->TimeH->formatTime($user['User']['registered'],
															'%d.%m.%Y')),
									];
									if ($user['UserOnline']['logged_in']) {
										$_u[] = __('Online');
									}
									if (!empty($user['User']['user_lock'])) {
										$_u[] = __('%s banned',
												$this->UserH->banned($user['User']['user_lock']));
									}
									echo $this->Html->nestedList($_u);
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
