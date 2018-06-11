<?php
if (!$CurrentUser->isLoggedIn()) {
    $register = $this->request->getAttribute('webroot') . 'users/register/';
    echo '<a href="' . $register . '" class="btn btn-link" rel="nofollow">';
    echo __('register_linkname');
    echo '</a>';

    $action = $this->request->getParam('action');
    if ($action !== 'login' && $action !== 'register') {
        echo $this->element('users/login_modal');
        ?>
        <a href="<?php echo $this->request->getAttribute('webroot'); ?>login/"
           id="showLoginForm" title="<?= __('login_btn') ?>"
           class='btn btn-link' rel="nofollow">
            <?= $this->Layout->textWithIcon(__('login_btn'), 'sign-in') ?>
        </a>
        <?php
    }
} else {
    ?>
    <a href="<?= $this->request->getAttribute('webroot'); ?>users/view/<?= $CurrentUser->getId() ?>"
       id="btn_view_current_user" class="btn btn-link">
        <?= $this->Layout->textWithIcon(__('user.b.profile'), 'user') ?>
    </a>
    <?php
    echo $this->Html->link(
        $this->Layout->textWithIcon(h(__('logout_linkname')), 'sign-out'),
        '/logout',
        ['id' => 'js-btnLogout', 'class' => 'btn btn-link', 'escape' => false]
    );
}
