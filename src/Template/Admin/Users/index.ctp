<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Users'), '/admin/users');
?>
<div class="users index">
    <h1><?= __('Users') ?></h1>
    <?=
    $this->Html->link(
        __('New User'),
        ['action' => 'add'],
        ['class' => 'btn']
    )
    ?>
    <hr/>
    <table id="usertable" class="table table-striped">
        <thead>
        <?php
        $tableHeaders = [
            __('username_marking'),
            __('user_type'),
            __('user_email'),
            __("registered"),
        ];
        if (Configure::read('Saito.Settings.block_user_ui')) :
            $tableHeaders[] = __('user.set.lock.t');
        endif;
        echo $this->Html->tableHeaders($tableHeaders);
        ?>
        </thead>
        <tbody>
        <?php
        $blockUi = Configure::read('Saito.Settings.block_user_ui');
        foreach ($users as $user) {
            $tableCells = [
                '<strong>' . $this->Html->link($user->get('username'), "/users/view/{$user->get('id')}") . '</strong>',
                $this->User->type($user->get('user_type')),
                $this->Html->link(
                    $user->get('user_email'),
                    'mailto:' . $user->get('user_email')
                ),
                // output date format sortable by datatable JS plugin
                $this->TimeH->formatTime(
                    $user->get('registered'),
                    '%Y-%m-%d %H:%M',
                    ['wrap' => false]
                )
            ];
            if ($blockUi) {
                // without the &nbsp; the JS-sorting with the datatables plugin doesn't work
                $tableCells[] = $this->User->banned($user->get('user_lock')) . '&nbsp;';
            }
            echo $this->Html->tableCells(
                [$tableCells],
                ['class' => 'a'],
                ['class' => 'b']
            );
        }
        ?>
        </tbody>
    </table>
</div>
<?php $this->Admin->jqueryTable('#usertable', "[[3, 'desc'], [0, 'asc']]"); ?>
