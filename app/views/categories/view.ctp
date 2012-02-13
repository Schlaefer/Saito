<div class="categories view">
<h2><?php echo __('Category');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $category['Category']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Category Order'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $category['Category']['category_order']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Category'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $category['Category']['category']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Description'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $category['Category']['description']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Accession'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $category['Category']['accession']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Standard Category'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $category['Category']['standard_category']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Category'), array('action' => 'edit', $category['Category']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Category'), array('action' => 'delete', $category['Category']['id']), null, sprintf(__('Are you sure you want to delete # %s?'), $category['Category']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Categories'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Category'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Entries'), array('controller' => 'entries', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Entry'), array('controller' => 'entries', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Entries');?></h3>
	<?php if (!empty($category['Entry'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Pid'); ?></th>
		<th><?php echo __('Tid'); ?></th>
		<th><?php echo __('Uniqid'); ?></th>
		<th><?php echo __('Time'); ?></th>
		<th><?php echo __('Last Answer'); ?></th>
		<th><?php echo __('Edited'); ?></th>
		<th><?php echo __('Edited By'); ?></th>
		<th><?php echo __('User Id'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('Subject'); ?></th>
		<th><?php echo __('Category'); ?></th>
		<th><?php echo __('Text'); ?></th>
		<th><?php echo __('Email Notify'); ?></th>
		<th><?php echo __('Locked'); ?></th>
		<th><?php echo __('Fixed'); ?></th>
		<th><?php echo __('Views'); ?></th>
		<th><?php echo __('No Text'); ?></th>
		<th><?php echo __('Flattr'); ?></th>
		<th class="actions"><?php echo __('Actions');?></th>
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
				<?php echo $this->Html->link(__('View'), array('controller' => 'entries', 'action' => 'view', $entry['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'entries', 'action' => 'edit', $entry['id'])); ?>
				<?php echo $this->Html->link(__('Delete'), array('controller' => 'entries', 'action' => 'delete', $entry['id']), null, sprintf(__('Are you sure you want to delete # %s?'), $entry['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Entry'), array('controller' => 'entries', 'action' => 'add'));?> </li>
		</ul>
	</div>
</div>
