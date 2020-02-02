<?php $this->Breadcrumbs->add(__('Smilies'), '/admin/smilies'); ?>
<?php $this->Breadcrumbs->add(__('Edit Smiley'), false); ?>
<div class="smilies form">
    <h1><?php echo __('Admin Edit Smiley'); ?></h1>
    <?php echo $this->Form->create($smiley); ?>
    <fieldset>
        <?php
        echo $this->Form->control('id');
        echo $this->Form->control('sort', ['label' => __('sort.order')]);
        echo $this->Form->control('icon');
        echo $this->Form->control('image');
        echo $this->Form->control('title');
        echo $this->Form->submit(__('Submit'), ['class' => 'btn btn-primary']);
        ?>
    </fieldset>
    <?php echo $this->Form->end(); ?>
</div>
