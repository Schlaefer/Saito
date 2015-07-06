<?php
$this->Html->addCrumb(__('Settings'), '/admin/settings');
$this->Html->addCrumb(__d('nondynamic', $setting->get('name')), '#');
?>
<h1><?php echo __d('nondynamic', $setting->get('name')); ?></h1>
<div class="row">
    <div class="span6">
        <?php
        echo $this->Form->create(
            $setting,
            ['inputDefaults' => [], 'class' => 'well']
        );
        echo $this->Form->input(
            'value',
            [
                'label' => __d('nondynamic', $setting->get('name')),
            ]
        );
        echo $this->Form->submit(
            __('Submit'),
            [
                'class' => 'btn-primary',
            ]
        );
        echo $this->Form->end();
        ?>
    </div>
    <div class="span4">
        <p><?php echo __d('nondynamic', $setting->get('name') . '_exp'); ?></p>
    </div>
</div>
