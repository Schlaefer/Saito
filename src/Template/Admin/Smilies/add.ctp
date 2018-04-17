<div class="smilies form">
    <?php $this->Breadcrumbs->add(__('Smilies'), '/admin/smilies'); ?>
    <?php $this->Breadcrumbs->add(__('Add Smiley'), '#'); ?>
    <h1><?php echo __('Add Smiley'); ?></h1>
    <?php echo $this->Form->create($smiley); ?>
    <fieldset>
        <?php
        echo $this->Form->control('icon', ['label' => __('Icon')]);
        echo $this->Form->control('image', ['label' => __('Image')]);
        echo $this->Form->control('title');
        echo $this->Form->control('order', ['label' => __('sort.order')]);
        echo $this->Form->submit(__('Submit'), ['class' => 'btn btn-primary']);
        ?>
    </fieldset>
    <?php echo $this->Form->end(); ?>
</div>
