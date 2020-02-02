<?php $this->Breadcrumbs->add(__('Smilies'), '/admin/smilies'); ?>
<?php $this->Breadcrumbs->add(__('Smiley Codes'), '/admin/smiley_codes'); ?>
<?php $this->Breadcrumbs->add(__('Add Smiley Code'), false); ?>
<div class="smileyCodes form">
    <?php echo $this->Form->create($smiley); ?>
    <fieldset>
        <legend><?php echo __('Add Smiley Code'); ?></legend>
        <?php
        echo $this->Form->control('smiley_id');
        echo $this->Form->control('code');
        echo $this->Form->submit(__('Submit'), ['class' => 'btn btn-primary'])
        ?>
    </fieldset>
    <?php echo $this->Form->end(); ?>
</div>
