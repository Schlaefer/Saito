<?php $this->Breadcrumbs->add(__('Categories'), '/admin/categories'); ?>
<?php $this->Breadcrumbs->add(__('Edit Category'), false); ?>

<div class="categories form">
    <h1><?php echo __('Edit Category'); ?></h1>
    <?= $this->element('Admin.Categories/edit', ['category' => $category]) ?>
</div>
