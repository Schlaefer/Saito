<div class="smilies view">
<h2><?php  __('Smiley');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $smiley['Smiley']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Order'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $smiley['Smiley']['order']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Icon'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $smiley['Smiley']['icon']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Image'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $smiley['Smiley']['image']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Title'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $smiley['Smiley']['title']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Smiley', true), array('action' => 'edit', $smiley['Smiley']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Smiley', true), array('action' => 'delete', $smiley['Smiley']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $smiley['Smiley']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Smilies', true), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Smiley', true), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Smiley Codes', true), array('controller' => 'smiley_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Smiley Code', true), array('controller' => 'smiley_codes', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php __('Related Smiley Codes');?></h3>
	<?php if (!empty($smiley['SmileyCode'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php __('Id'); ?></th>
		<th><?php __('Smiley Id'); ?></th>
		<th><?php __('Code'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($smiley['SmileyCode'] as $smileyCode):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $smileyCode['id'];?></td>
			<td><?php echo $smileyCode['smiley_id'];?></td>
			<td><?php echo $smileyCode['code'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View', true), array('controller' => 'smiley_codes', 'action' => 'view', $smileyCode['id'])); ?>
				<?php echo $this->Html->link(__('Edit', true), array('controller' => 'smiley_codes', 'action' => 'edit', $smileyCode['id'])); ?>
				<?php echo $this->Html->link(__('Delete', true), array('controller' => 'smiley_codes', 'action' => 'delete', $smileyCode['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $smileyCode['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Smiley Code', true), array('controller' => 'smiley_codes', 'action' => 'add'));?> </li>
		</ul>
	</div>
</div>
