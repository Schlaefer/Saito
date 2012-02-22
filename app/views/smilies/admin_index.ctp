<?php $this->Html->addCrumb(__('Smilies', true), '/admin/smilies'); ?>
<div class="smilies index">
	<h2><?php __('Smilies');?></h2>
	<table cellpadding="0" cellspacing="0" class="table table-striped table-bordered table-condensed">
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
			<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', $smiley['Smiley']['id']), array('class' => 'btn')); ?>
			<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $smiley['Smiley']['id']), array('class' => 'btn'), sprintf(__('Are you sure you want to delete # %s?', true), $smiley['Smiley']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
</div>
		<?php echo $this->Html->link(__('New Smiley', true), array('action' => 'add'), array('class' => 'btn')); ?> &nbsp; | &nbsp;
		<?php echo $this->Html->link(__('List Smiley Codes', true), array('controller' => 'smiley_codes', 'action' => 'index'), array('class'=>'btn')); ?>