<div class="smilies form">
    <?php $this->Html->addCrumb(__('Smilies'), '/admin/smilies'); ?>
    <?php $this->Html->addCrumb(__('Add Smiley'), '#'); ?>
    <h1><?php echo __('Add Smiley'); ?></h1>
    <?php echo $this->Form->create($smiley); ?>
    <fieldset>
        <?php
        echo $this->Form->input('icon', ['label' => __('Icon')]);
        echo $this->Form->input('image', ['label' => __('Image')]);
        echo $this->Form->input('title');
        echo $this->Form->input('order', ['label' => __('sort.order')]);
        echo $this->Form->submit(__('Submit'), ['class' => 'btn btn-primary']);
        ?>
    </fieldset>
    <?php echo $this->Form->end(); ?>
</div>
