<?php $this->Breadcrumbs->add(__('Users'), '/admin/users'); ?>
<?php $this->Breadcrumbs->add(__('Delete User'), false); ?>
<h1>Delete User <em><?= h($user->get('username')) ?></em></h1>

<div class='row'>
    <div class="card">
        <div class="card-body">
            <p>
                You are about to delete the user <em><?= h($user->get('username')) ?></em>.
            </p>
            <ul>
                <li>
                    His/her uploads will be deleted.
                </li>
                <li>
                    His/her profil data will be deleted.
                </li>
                <li>
                    His/her entries will remain with an unknown user as origin.
                </li>
            </ul>
            <?=
            $this->Html->link(
                __("Delete User {0}", $user->get('username')),
                '#deleteModal',
                ['class' => 'btn btn-danger', 'data-toggle' => 'modal']
            );
?>
        </div>
    </div>
</div>

<div id="deleteModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            Delete user <?= h($user->get('username')) ?>
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
        echo $this->Form->hidden('modeDelete', ['value' => 1]);

        $this->Form->setTemplates(['submitContainer' => '{{content}}']);
        echo $this->Form->submit(
            __('Make It So'),
            ['class' => 'btn btn-danger']
        );
        echo ' ';
        echo $this->Form->button(
            __('Abort'),
            ['class' => 'btn', 'data-dismiss' => 'modal']
        );
        echo $this->Form->end();
        ?>
      </div>
    </div>
  </div>
</div>
