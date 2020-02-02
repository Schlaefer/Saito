<?php
$this->Breadcrumbs->add(__('Settings'), '/admin/settings');
$this->Breadcrumbs->add(__d('nondynamic', $setting->get('name')), false);
?>
<h1><?php echo __d('nondynamic', $setting->get('name')); ?></h1>
<p><?php echo __d('nondynamic', $setting->get('name') . '_exp'); ?></p>
<?php
echo $this->Form->create(
    $setting,
    ['inputDefaults' => [], 'class' => 'well']
);

if ($setting->get('type') === 'bool') {
    $checkbox = $this->Form->control(
        'value',
        [
            'class' => 'form-check-input',
            'label' => __d('nondynamic', $setting->get('name')),
            'type' => 'checkbox',
        ]
    );
    echo $this->Html->div('form-check', $checkbox);
} else {
    echo $this->Form->control(
        'value',
        [
            'label' => __d('nondynamic', $setting->get('name')),
        ]
    );
}

echo $this->Form->submit(
    __('Submit'),
    [
        'class' => 'btn-primary',
    ]
);
echo $this->Form->end();
?>
