<div class="smileyCodes view">
<h2><?php  __('Smiley Code');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $smileyCode['SmileyCode']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Smiley'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($smileyCode['Smiley']['title'], array('controller' => 'smilies', 'action' => 'view', $smileyCode['Smiley']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Code'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $smileyCode['SmileyCode']['code']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Smiley Code', true), array('action' => 'edit', $smileyCode['SmileyCode']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Smiley Code', true), array('action' => 'delete', $smileyCode['SmileyCode']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $smileyCode['SmileyCode']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Smiley Codes', true), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Smiley Code', true), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Smilies', true), array('controller' => 'smilies', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Smiley', true), array('controller' => 'smilies', 'action' => 'add')); ?> </li>
	</ul>
</div>
