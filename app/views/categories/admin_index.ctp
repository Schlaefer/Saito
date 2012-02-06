<?php $this->Html->addCrumb(__('Categories', true), '/admin/categories'); ?>
<div class="categories index">
	<h2><?php __('Categories');?></h2>
	<table cellpadding="0" cellspacing="0" class="table table-striped table-bordered table-condensed">
	<tr>
			<th><?php echo $this->Paginator->sort('id');?></th>
			<th><?php echo $this->Paginator->sort('category_order');?></th>
			<th><?php echo $this->Paginator->sort('category');?></th>
			<th><?php echo $this->Paginator->sort('description');?></th>
			<th><?php echo $this->Paginator->sort('accession');?></th>
			<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($categories as $category):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $category['Category']['id']; ?>&nbsp;</td>
		<td><?php echo $category['Category']['category_order']; ?>&nbsp;</td>
		<td><?php echo $category['Category']['category']; ?>&nbsp;</td>
		<td><?php echo $category['Category']['description']; ?>&nbsp;</td>
		<td><?php echo $category['Category']['accession']; ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', $category['Category']['id']), array('class' => 'btn disabled')); ?>
			<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete1', $category['Category']['id']), array('class' => 'btn btn-danger disabled'), sprintf(__('Are you sure you want to delete # %s?', true), $category['Category']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
		<span class="label label-info">Accesssion:</span> 0 Public, 1 User, 2 Mod u. Admins <!-- @lo -->
	</p>
</div>
	<hr/>
	<p>
		<?php echo $this->Html->link(__('New Category', true), array('action' => 'add'), array('class' => 'btn')); ?>
	</p>