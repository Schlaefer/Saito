<div class="groups view">
<h2><?php  __('Group');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $group['Group']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $group['Group']['name']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Created'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $group['Group']['created']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Modified'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $group['Group']['modified']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Group', true), array('action' => 'edit', $group['Group']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Group', true), array('action' => 'delete', $group['Group']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $group['Group']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Groups', true), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Group', true), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users', true), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User', true), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php __('Related Users');?></h3>
	<?php if (!empty($group['User'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php __('Id'); ?></th>
		<th><?php __('User Type'); ?></th>
		<th><?php __('Group Id'); ?></th>
		<th><?php __('Username'); ?></th>
		<th><?php __('User Real Name'); ?></th>
		<th><?php __('Password'); ?></th>
		<th><?php __('User Email'); ?></th>
		<th><?php __('Hide Email'); ?></th>
		<th><?php __('User Hp'); ?></th>
		<th><?php __('User Place'); ?></th>
		<th><?php __('Signature'); ?></th>
		<th><?php __('Profile'); ?></th>
		<th><?php __('Entry Count'); ?></th>
		<th><?php __('Logins'); ?></th>
		<th><?php __('Last Login'); ?></th>
		<th><?php __('Last Logout'); ?></th>
		<th><?php __('Registered'); ?></th>
		<th><?php __('Last Refresh'); ?></th>
		<th><?php __('Last Refresh Tmp'); ?></th>
		<th><?php __('User View'); ?></th>
		<th><?php __('New Posting Notify'); ?></th>
		<th><?php __('New User Notify'); ?></th>
		<th><?php __('Personal Messages'); ?></th>
		<th><?php __('Time Difference'); ?></th>
		<th><?php __('User Lock'); ?></th>
		<th><?php __('Pwf Code'); ?></th>
		<th><?php __('Activate Code'); ?></th>
		<th><?php __('User Font Size'); ?></th>
		<th><?php __('User Signatures Hide'); ?></th>
		<th><?php __('User Signatures Images Hide'); ?></th>
		<th><?php __('User Categories'); ?></th>
		<th><?php __('User Forum Refresh Time'); ?></th>
		<th><?php __('User Forum Hr Ruler'); ?></th>
		<th><?php __('User Automaticaly Mark As Read'); ?></th>
		<th><?php __('User Sort Last Answer'); ?></th>
		<th><?php __('User Color New Postings'); ?></th>
		<th><?php __('User Color Actual Posting'); ?></th>
		<th><?php __('User Color Old Postings'); ?></th>
		<th><?php __('User Show Own Signature'); ?></th>
		<th><?php __('Slidetab Order'); ?></th>
		<th><?php __('Show Userlist'); ?></th>
		<th><?php __('Show Recentposts'); ?></th>
		<th><?php __('Show About'); ?></th>
		<th><?php __('Show Donate'); ?></th>
		<th><?php __('Inline View On Click'); ?></th>
		<th><?php __('Flattr Uid'); ?></th>
		<th><?php __('Flattr Allow User'); ?></th>
		<th><?php __('Flattr Allow Posting'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($group['User'] as $user):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $user['id'];?></td>
			<td><?php echo $user['user_type'];?></td>
			<td><?php echo $user['group_id'];?></td>
			<td><?php echo $user['username'];?></td>
			<td><?php echo $user['user_real_name'];?></td>
			<td><?php echo $user['password'];?></td>
			<td><?php echo $user['user_email'];?></td>
			<td><?php echo $user['hide_email'];?></td>
			<td><?php echo $user['user_hp'];?></td>
			<td><?php echo $user['user_place'];?></td>
			<td><?php echo $user['signature'];?></td>
			<td><?php echo $user['profile'];?></td>
			<td><?php echo $user['entry_count'];?></td>
			<td><?php echo $user['logins'];?></td>
			<td><?php echo $user['last_login'];?></td>
			<td><?php echo $user['last_logout'];?></td>
			<td><?php echo $user['registered'];?></td>
			<td><?php echo $user['last_refresh'];?></td>
			<td><?php echo $user['last_refresh_tmp'];?></td>
			<td><?php echo $user['user_view'];?></td>
			<td><?php echo $user['new_posting_notify'];?></td>
			<td><?php echo $user['new_user_notify'];?></td>
			<td><?php echo $user['personal_messages'];?></td>
			<td><?php echo $user['time_difference'];?></td>
			<td><?php echo $user['user_lock'];?></td>
			<td><?php echo $user['pwf_code'];?></td>
			<td><?php echo $user['activate_code'];?></td>
			<td><?php echo $user['user_font_size'];?></td>
			<td><?php echo $user['user_signatures_hide'];?></td>
			<td><?php echo $user['user_signatures_images_hide'];?></td>
			<td><?php echo $user['user_categories'];?></td>
			<td><?php echo $user['user_forum_refresh_time'];?></td>
			<td><?php echo $user['user_forum_hr_ruler'];?></td>
			<td><?php echo $user['user_automaticaly_mark_as_read'];?></td>
			<td><?php echo $user['user_sort_last_answer'];?></td>
			<td><?php echo $user['user_color_new_postings'];?></td>
			<td><?php echo $user['user_color_actual_posting'];?></td>
			<td><?php echo $user['user_color_old_postings'];?></td>
			<td><?php echo $user['user_show_own_signature'];?></td>
			<td><?php echo $user['slidetab_order'];?></td>
			<td><?php echo $user['show_userlist'];?></td>
			<td><?php echo $user['show_recentposts'];?></td>
			<td><?php echo $user['show_about'];?></td>
			<td><?php echo $user['show_donate'];?></td>
			<td><?php echo $user['inline_view_on_click'];?></td>
			<td><?php echo $user['flattr_uid'];?></td>
			<td><?php echo $user['flattr_allow_user'];?></td>
			<td><?php echo $user['flattr_allow_posting'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View', true), array('controller' => 'users', 'action' => 'view', $user['id'])); ?>
				<?php echo $this->Html->link(__('Edit', true), array('controller' => 'users', 'action' => 'edit', $user['id'])); ?>
				<?php echo $this->Html->link(__('Delete', true), array('controller' => 'users', 'action' => 'delete', $user['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $user['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New User', true), array('controller' => 'users', 'action' => 'add'));?> </li>
		</ul>
	</div>
</div>
