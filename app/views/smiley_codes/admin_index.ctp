<div class="smileyCodes index">
	<h2><?php __('Smiley Codes');?></h2>
	<table cellpadding="0" cellspacing="0" class="table table-striped table-bordered table-condensed">
	<tr>
			<th><?php echo $this->Paginator->sort('id');?></th>
			<th><?php echo $this->Paginator->sort('smiley_id');?></th>
			<th><?php echo $this->Paginator->sort('code');?></th>
			<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($smileyCodes as $smileyCode):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $smileyCode['SmileyCode']['id']; ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($smileyCode['Smiley']['icon'], array('controller' => 'smilies', 'action' => 'view', $smileyCode['Smiley']['id'])); ?>
		</td>
		<td><?php echo $smileyCode['SmileyCode']['code']; ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View', true), array('action' => 'view', $smileyCode['SmileyCode']['id']), array('class' => 'btn')); ?>
			<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', $smileyCode['SmileyCode']['id']), array('class' => 'btn')); ?>
			<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $smileyCode['SmileyCode']['id']), array('class' => 'btn'), sprintf(__('Are you sure you want to delete # %s?', true), $smileyCode['SmileyCode']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Smiley Code', true), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Smilies', true), array('controller' => 'smilies', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Smiley', true), array('controller' => 'smilies', 'action' => 'add')); ?> </li>
	</ul>
</div>