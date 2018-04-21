<?php
if (!isset($divider)) {
    $divider = '';
}
if (!$CurrentUser->isLoggedIn()) {
    $register = $this->request->getAttribute('webroot') . 'users/register/';
    echo '<a href="' . $register . '" class="top-menu-item" rel="nofollow">';
    echo __('register_linkname');
    echo '</a>';

    $action = $this->request->getParam('action');
    if ($action !== 'login') {
        echo $divider;
        ?>
        <a href="<?php echo $this->request->getAttribute('webroot'); ?>login/"
           id="showLoginForm" title="<?= __('login_btn') ?>"
           class='top-menu-item' rel="nofollow">
            <?= $this->Layout->textWithIcon(__('login_btn'), 'sign-in') ?>
        </a>
    <?php
    }
} else {
    if ($CurrentUser->permission('saito.core.admin.backend')) {
        echo $this->Html->link(
            $this->Layout->textWithIcon(h(__('ial.aa')), 'wrench'),
            '/admin',
            ['class' => 'top-menu-item', 'escape' => false]
        );
        echo $divider;
    }
    ?>
    <a href="<?= $this->request->getAttribute('webroot'); ?>users/view/<?= $CurrentUser->getId() ?>"
       id="btn_view_current_user" class="top-menu-item">
        <?= $this->Layout->textWithIcon(__('user.b.profile'), 'user') ?>
    </a>
    <?php
    //= show additional nav-buttons
    $items = $SaitoEventManager->dispatch(
        'Request.Saito.View.MainMenu.navItem',
        ['View' => $this]
    );
    if ($items) {
        foreach ($items as $item) {
            echo $divider;
            $link = $this->request->getAttribute('webroot') . $item['url'];
            echo "<a href=\"{$link}\" class=\"top-menu-item\">{$item['title']}";
            echo '</a>';
        }
    }
    ?>
    <?= $divider ?>
    <?php
    echo $this->Html->link(
        $this->Layout->textWithIcon(h(__('logout_linkname')), 'sign-out'),
        '/logout',
        ['id' => 'btn_logout', 'class' => 'top-menu-item', 'escape' => false]
    );
}
