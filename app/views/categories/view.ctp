<div class="categories view">
<h2><?php  __('Category');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $category['Category']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Category Order'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $category['Category']['category_order']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Category'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $category['Category']['category']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Description'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $category['Category']['description']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Accession'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $category['Category']['accession']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Standard Category'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $category['Category']['standard_category']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Category', true), array('action' => 'edit', $category['Category']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Category', true), array('action' => 'delete', $category['Category']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $category['Category']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Categories', true), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Category', true), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Entries', true), array('controller' => 'entries', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Entry', true), array('controller' => 'entries', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php __('Related Entries');?></h3>
	<?php if (!empty($category['Entry'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php __('Created'); ?></th>
		<th><?php __('Modified'); ?></th>
		<th><?php __('Id'); ?></th>
		<th><?php __('Pid'); ?></th>
		<th><?php __('Tid'); ?></th>
		<th><?php __('Uniqid'); ?></th>
		<th><?php __('Time'); ?></th>
		<th><?php __('Last Answer'); ?></th>
		<th><?php __('Edited'); ?></th>
		<th><?php __('Edited By'); ?></th>
		<th><?php __('User Id'); ?></th>
		<th><?php __('Name'); ?></th>
		<th><?php __('Subject'); ?></th>
		<th><?php __('Category'); ?></th>
		<th><?php __('Text'); ?></th>
		<th><?php __('Email Notify'); ?></th>
		<th><?php __('Locked'); ?></th>
		<th><?php __('Fixed'); ?></th>
		<th><?php __('Views'); ?></th>
		<th><?php __('No Text'); ?></th>
		<th><?php __('Flattr'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($category['Entry'] as $entry):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $entry['created'];?></td>
			<td><?php echo $entry['modified'];?></td>
			<td><?php echo $entry['id'];?></td>
			<td><?php echo $entry['pid'];?></td>
			<td><?php echo $entry['tid'];?></td>
			<td><?php echo $entry['uniqid'];?></td>
			<td><?php echo $entry['time'];?></td>
			<td><?php echo $entry['last_answer'];?></td>
			<td><?php echo $entry['edited'];?></td>
			<td><?php echo $entry['edited_by'];?></td>
			<td><?php echo $entry['user_id'];?></td>
			<td><?php echo $entry['name'];?></td>
			<td><?php echo $entry['subject'];?></td>
			<td><?php echo $entry['category'];?></td>
			<td><?php echo $entry['text'];?></td>
			<td><?php echo $entry['email_notify'];?></td>
			<td><?php echo $entry['locked'];?></td>
			<td><?php echo $entry['fixed'];?></td>
			<td><?php echo $entry['views'];?></td>
			<td><?php echo $entry['no_text'];?></td>
			<td><?php echo $entry['flattr'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View', true), array('controller' => 'entries', 'action' => 'view', $entry['id'])); ?>
				<?php echo $this->Html->link(__('Edit', true), array('controller' => 'entries', 'action' => 'edit', $entry['id'])); ?>
				<?php echo $this->Html->link(__('Delete', true), array('controller' => 'entries', 'action' => 'delete', $entry['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $entry['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Entry', true), array('controller' => 'entries', 'action' => 'add'));?> </li>
		</ul>
	</div>
</div>
