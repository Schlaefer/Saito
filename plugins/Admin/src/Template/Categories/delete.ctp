<?php $this->Breadcrumbs->add(__('Categories'), '/admin/categories'); ?>
<?php $this->Breadcrumbs->add(__('Delete Category'), '#'); ?>
<h1>Delete Category <em><?= $category->get('category') ?></em></h1>

<p>
    You're going to delete the category
    <em><?= $category->get('category') ?></em>.
    You must decide what shall happen with the threads in this category:
</p>

<div class='row'>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h3> Move threads to different category </h3>

                <p>
                    Deletes the category but moves all entries into another
                    category.
                </p>
                <?php
                echo $this->Form->create(null);
                echo $this->Form->label('targetCategory', 'Move to Category:');
                echo $this->Form->control('targetCategory', ['type' => 'select']);
                echo $this->Form->hidden('mode', ['value' => 'move']);
                echo $this->Form->submit(
                    __('Move entries and delete category'),
                    ['class' => 'btn btn-primary']
                );
                echo $this->Form->end();
                ?>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
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
</div>


<div id="deleteModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            Delete threads and delete category
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
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
        echo ' ';
        echo $this->Form->button(
            __('Make It So'),
            ['type' => 'submit', 'class' => 'btn btn-danger']
        );
        echo $this->Form->end();
        ?>
      </div>
    </div>
  </div>
</div>

