<?php $this->Html->addCrumb(__('Categories'), '/admin/categories'); ?>
<?php $this->Html->addCrumb(__('Delete Category'), '#'); ?>
<h1>Delete Category <em><?= $category->get('category') ?></em></h1>
<p>
    You're going to delete the category
    <em><?= $category->get('category') ?></em>.
    You must decide what shall happen with the threads in this category:
</p>

<div class='row'>
    <div class="span5">
        <div class="well">
            <h3> Move threads to different category </h3>

            <p>
                Deletes the category but moves all entries into another
                category.
            </p>
            <?php
            echo $this->Form->create(null);
            echo $this->Form->label('targetCategory', 'Move to Cateogry:');
            echo $this->Form->select('targetCategory', $targetCategories);
            echo $this->Form->hidden('mode', ['value' => 'move']);
            echo $this->Form->submit(
                __('Move entries and delete category'),
                ['class' => 'btn btn-primary']
            );
            echo $this->Form->end();
            ?>
        </div>
    </div>
    <div class="span5">
        <div class="well">

            <h3> Delete threads</h3>

            <p>
                Deletes the category and all threads in it.
            </p>
            <?php
            echo $this->Html->link(
                __("Delete entries and delete category"),
                '#deleteModal',
                [
                    'class' => 'btn btn-danger',
                    'data-toggle' => 'modal',
                ]
            );
            ?>
        </div>
    </div>
</div>
<div id="deleteModal" class="modal hide fade">
    <div class="modal-header">
        <a class="close" data-dismiss="modal">×</a>

        <h3>
            Delete threads and delete category
        </h3>
    </div>
    <div class="modal-body">
        <div class="alert alert-error"> This action can't be undone! Are you
            sure?
        </div>
    </div>
    <div class="modal-footer">
        <?php
        echo $this->Form->create();
        echo $this->Form->hidden('mode', ['value' => 'delete']);
        echo $this->Form->button(
            __('Abort'),
            ['class' => 'btn', 'data-dismiss' => 'modal']
        );
        echo $this->Form->button(
            __('Make It So'),
            ['type' => 'submit', 'class' => 'btn btn-danger']
        );
        echo $this->Form->end();
        ?>
    </div>
</div>
