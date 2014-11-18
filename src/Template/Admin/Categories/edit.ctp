<?php $this->Html->addCrumb(__('Categories'), '/admin/categories'); ?>
<?php $this->Html->addCrumb(__('Edit Category'), '#'); ?>
<div class="categories form">
    <h1><?php echo __('Edit Category'); ?></h1>
    <?= $this->element('Admin/Categories/edit', ['category' => $category]) ?>
</div>
