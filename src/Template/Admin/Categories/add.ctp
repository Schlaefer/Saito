<?php
$this->Breadcrumbs->add(__('Categories'), '/admin/categories');
$this->Breadcrumbs->add(__('Add Category'), '/admin/categories/add');

$html = $this->Html->tag('h1', __('Add Category'));
$form = $this->element('Admin/Categories/edit', ['category' => $category]);
$html .= $this->Html->tag('fieldset', $form, ['escape' => false]);

echo $this->Html->tag('div', $html, ['class' => 'categores form']);
