<div id="settings_index" class="settings index">
	<h2>Forum</h2>
	<table class="table table-striped table-bordered table-condensed">
		<?php echo $this->Html->tableHeaders(array('Key', 'Value', 'Explanation', 'Actions')); ?>
		<tr>
			<td>
				<?php echo __('forum_disabled'); ?>
			</td>
			<td>
				<?php echo $Settings['forum_disabled']; ?>
			</td>
			<td>
				<p><?php echo __('forum_disabled_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'forum_disabled' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('forum_disabled_text'); ?>
			</td>
			<td>
				<?php echo $Settings['forum_disabled_text']; ?>
			</td>
			<td>
				<p><?php echo __('forum_disabled_text_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'forum_disabled_text'),
								array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('timezone'); ?>
			</td>
			<td>
				<?php echo $Settings['timezone']; ?>
			</td>
			<td>
				<p><?php echo __('timezone_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'timezone'),
								array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
	</table> <!--	</table>-->
	<br />
	<h2>Misc</h2>
	<table class="table table-striped table-bordered table-condensed">
		<?php echo $this->Html->tableHeaders(array('Key', 'Value', 'Explanation', 'Actions')); ?>
		<?php foreach( $autoSettings as $k => $v ) : ?>
		<tr>
			<td>
				<?php echo __d('nondynamic', $k); ?>
			</td>
			<td>
				<?php echo $v; ?>
			</td>
			<td>
				<p><?php echo __d('nondynamic', $k.'_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', $k),
								array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<?php	endforeach; ?>
	</table> <!--	</table>-->
	<br />
	<h2>Flattr</h2>
	<table class="table table-striped table-bordered table-condensed">
		<?php echo $this->Html->tableHeaders(array('Key', 'Value', 'Explanation', 'Actions')); ?>
		<tr>
			<td>
				<?php echo __('flattr_enabled'); ?>
			</td>
			<td>
				<?php echo $Settings['flattr_enabled']; ?>
			</td>
			<td>
				<p><?php echo __('flattr_enabled_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'flattr_enabled'),
								array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('flattr_language'); ?>
			</td>
			<td>
				<?php echo $Settings['flattr_language']; ?>
			</td>
			<td>
				<p><?php echo __('flattr_language_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'flattr_language'),
								array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('flattr_category'); ?>
			</td>
			<td>
				<?php echo $Settings['flattr_category']; ?>
			</td>
			<td>
				<p><?php echo __('flattr_category_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'flattr_category'),
								array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
	</table> <!--	</table>-->
	<br/>
	<h2>Uploads</h2>
	<table class="table table-striped table-bordered table-condensed">
		<?php echo $this->Html->tableHeaders(array('Key', 'Value', 'Explanation', 'Actions')); ?>
		<tr>
			<td>
				<?php echo __('upload_max_img_size'); ?>
			</td>
			<td>
				<?php echo $Settings['upload_max_img_size']; ?>
			</td>
			<td>
				<p><?php echo __('upload_max_img_size_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'upload_max_img_size'),
								array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('upload_max_number_of_uploads'); ?>
			</td>
			<td>
				<?php echo $Settings['upload_max_number_of_uploads']; ?>
			</td>
			<td>
				<p><?php echo __('upload_max_number_of_uploads_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'upload_max_number_of_uploads'),
								array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
	</table> <!--	</table>-->
</div>