<div class="smilies view">
<h2><?php  __('Smily');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $smily['Smily']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Order'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $smily['Smily']['order']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Icon'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $smily['Smily']['icon']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Image'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $smily['Smily']['image']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Title'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $smily['Smily']['title']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Smily', true), array('action' => 'edit', $smily['Smily']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Smily', true), array('action' => 'delete', $smily['Smily']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $smily['Smily']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Smilies', true), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Smily', true), array('action' => 'add')); ?> </li>
	</ul>
</div>
