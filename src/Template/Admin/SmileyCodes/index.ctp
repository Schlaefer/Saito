<?php $this->Html->addCrumb(__('Smilies'), '/admin/smilies'); ?>
<?php $this->Html->addCrumb(__('Smiley Codes'), '/admin/smiley_codes'); ?>
<div class="smileyCodes index">
	<h1><?php echo __('Smiley Codes');?></h1>
	<?php echo $this->Html->link(__('New Smiley Code'), array('action' => 'add'), array('class' => 'btn')); ?>
	<hr/>
	<table cellpadding="0" cellspacing="0" class="table table-striped table-bordered table-condensed">
	<tr>
			<th><?php echo $this->Paginator->sort('id');?></th>
			<th><?php echo $this->Paginator->sort('smiley_id');?></th>
			<th><?php echo $this->Paginator->sort('code');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
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
		<td><?php echo $smileyCode->get('id'); ?>&nbsp;</td>
		<td>
            <?php echo $this->Html->link(
                $smileyCode->get('smiley')->get('icon'),
                [
                    'controller' => 'smilies',
                    'action' => 'edit',
                    $smileyCode->get('smiley')->get('id')
                ]
            ); ?>
		</td>
		<td><?php echo $smileyCode->get('code'); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $smileyCode->get('id')), array('class' => 'btn')); ?>
			<?php echo $this->Html->link(__('Delete'), array('action' => 'delete', $smileyCode->get('id')), array('class' => 'btn'), sprintf(__('Are you sure you want to delete # %s?'), $smileyCode->get('id'))); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
</div>
