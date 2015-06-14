<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack();
$this->end();

$this->element('users/menu');
?>

<div class="user index">
    <div class="panel">
        <?= $this->Layout->panelHeading($title_for_page, ['pageHeading' => true]) ?>
        <div class="panel-content">
            <div class="table-menu sort-menu">
                <?php
                foreach ($menuItems as $field => $item) {
                    list($title, $options) = $item;
                    $menu[] = $this->Paginator->sort($field, $title, $options);
                }
                echo __('Sort by: {0}', implode(', ', $menu));
                ?>
            </div>
            <table class="table th-left row-sep">
                <tbody>
                <?php
                foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <?=
                            $this->Html->link(
                                $user->get('username'),
                                '/users/view/' . $user->get('id')
                            );
                            ?>
                        </td>
                        <td>
                            <?php
                            $_u = [
                                $this->User->type($user->get('user_type')),
                                __(
                                    'user_since {0}',
                                    $this->TimeH->formatTime(
                                        $user->get('registered'),
                                        '%d.%m.%Y'
                                    )
                                ),
                            ];
                            if ($user->get('user_online') && $user->get('user_online')['logged_in']) {
                                $_u[] = __('Online');
                            }
                            if ($user->get('username') === 'Frogurt') {
                                $a = 1;
                            }
                            if ($user->isLocked()) {
                                $_u[] = __(
                                    '{0} banned',
                                    $this->User->banned(true)
                                );
                            }
                            echo $this->Html->nestedList($_u);
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
