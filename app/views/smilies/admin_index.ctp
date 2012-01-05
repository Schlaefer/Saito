<div class="smilies index">
	<h2><?php __('Smilies');?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id');?></th>
			<th><?php echo $this->Paginator->sort('order');?></th>
			<th><?php echo $this->Paginator->sort('icon');?></th>
			<th><?php echo $this->Paginator->sort('image');?></th>
			<th><?php echo $this->Paginator->sort('title');?></th>
			<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($smilies as $smiley):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $smiley['Smiley']['id']; ?>&nbsp;</td>
		<td><?php echo $smiley['Smiley']['order']; ?>&nbsp;</td>
		<td><?php echo $smiley['Smiley']['icon']; ?>&nbsp;</td>
		<td><?php echo $smiley['Smiley']['image']; ?>&nbsp;</td>
		<td><?php echo $smiley['Smiley']['title']; ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View', true), array('action' => 'view', $smiley['Smiley']['id'])); ?>
			<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', $smiley['Smiley']['id'])); ?>
			<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $smiley['Smiley']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $smiley['Smiley']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
	));
	?>	</p>

	<div class="paging">
		<?php echo $this->Paginator->prev('<< ' . __('previous', true), array(), null, array('class'=>'disabled'));?>
	 | 	<?php echo $this->Paginator->numbers();?>
 |
		<?php echo $this->Paginator->next(__('next', true) . ' >>', array(), null, array('class' => 'disabled'));?>
	</div>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Smiley', true), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Smiley Codes', true), array('controller' => 'smiley_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Smiley Code', true), array('controller' => 'smiley_codes', 'action' => 'add')); ?> </li>
	</ul>
</div>