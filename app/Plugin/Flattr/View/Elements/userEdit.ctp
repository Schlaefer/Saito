<div class="panel">
	<?= $this->Layout->panelHeading(__d('flattr', 'flattr')) ?>
	<div class='panel-content panel-form'>
		<table class="table th-left elegant">
			<tr>
				<td><?= __d('flattr', 'flattr_uid') ?></td>
				<td><?= $this->Form->input('flattr_uid', ['label' => false]); ?> </td>
			</tr>
			<tr>
				<td><?= __d('flattr', 'flattr_allow_user') ?></td>
				<td>
					<?= $this->Form->checkbox('flattr_allow_user', ['label' => false]); ?>
					<p class="exp">
						<?= __d('flattr', 'flattr_allow_user_exp') ?>
					</p>
				</td>
			</tr>
			<tr>
				<td><?= __d('flattr', 'flattr_allow_posting'); ?></td>
				<td>
					<?= $this->Form->checkbox('flattr_allow_posting', ['label' => false]); ?>
					<p class="exp">
						<?= __d('flattr', 'flattr_allow_posting_exp') ?>
					</p>
				</td>
			</tr>
		</table>
	</div>
</div>


