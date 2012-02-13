
<div data-role="page" data-add-back-btn="false">

	<div data-role="header">
		<h1><?php echo $title_for_layout ?></h1>
	</div><!-- /header -->

	<div data-role="content">
		<?php echo $this->Form->create('Entry', array('url' => '/entries/add.xml') ); ?>
		<?php echo $this->EntryH->getCategorySelectForEntry($categories, $this->request->data); ?>
		<?php echo $this->Form->input('subject'); ?>
		<?php echo $this->Form->input('text'); ?>
		<?php echo $this->Form->hidden('pid'); ?>
		<?php echo $this->Form->hidden('mobile', array('value' => 1)); ?>
		<?php echo $this->Form->submit(); ?>
		<?php echo $this->Form->end(); ?>
	</div>