<?php $this->Breadcrumbs->add(__('Categories'), false); ?>
<div class="categories index">
<h1><?= __('Categories') ?></h1>
<p>
    <?=
    $this->Html->link(
        __('New Category'),
        ['action' => 'add'],
        ['class' => 'btn btn-primary']
    )
?>
</p>
<hr/>
<table cellpadding="0" cellspacing="0"
        class="table table-striped table-bordered table-condensed">
    <tr>
        <th><?= $this->Paginator->sort('category_order', __('sort.order')); ?></th>
        <th><?= $this->Paginator->sort('category'); ?></th>
        <th><?= $this->Paginator->sort('description', __('description')); ?></th>
        <th><?= $this->Paginator->sort('accession', __('accession.read')); ?></th>
        <th><?= $this->Paginator->sort('accession_new_thread', __('accession.new_thread')); ?></th>
        <th><?= $this->Paginator->sort('accession_new_posting', __('accession.new_posting')); ?></th>
        <th class="actions"><?= __('Actions'); ?></th>
    </tr>
    <?php
    $i = 0;
    foreach ($categories as $category) :
        $class = '';
        if ($i++ % 2 == 0) {
            $class = ' class="altrow"';
        }
        ?>
        <tr<?= $class; ?>>
            <td><?= $category->get('category_order'); ?>
                &nbsp;</td>
            <td><?= h($category->get('category')) ?>&nbsp;</td>
            <td><?= h($category->get('description')) ?> &nbsp;</td>
            <td><?= h(__d('nondynamic', 'permission.role.' . $category->get('accession'))) ?>&nbsp;</td>
            <td><?= h(__d('nondynamic', 'permission.role.' . $category->get('accession_new_thread'))) ?>&nbsp;</td>
            <td><?= h(__d('nondynamic', 'permission.role.' . $category->get('accession_new_posting'))) ?>&nbsp;</td>
            <td class="actions">
                <?php
                echo $this->Html->link(
                    __('Edit'),
                    ['action' => 'edit', $category->get('id')],
                    ['class' => 'btn btn-warning']
                );
                echo " ";
                echo $this->Html->link(
                    __('Delete'),
                    ['action' => 'delete', $category->get('id')],
                    ['class' => 'btn btn-danger']
                );
                ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</div>
