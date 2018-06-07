<div class="form-group">
    <?= $this->Form->control('username', [
        'autocomplete' => 'username',
        'class' => 'form-control',
        'label' => __('register_user_name'),
        'tabindex' => 1
    ]) ?>
</div>
<div class="form-group">
    <?= $this->Form->control('user_email', [
        'autocomplete' => 'email',
        'class' => 'form-control',
        'label' => __('register_user_email'),
        'tabindex' => 2
    ]) ?>
</div>
<div class="form-group">
    <?= $this->Form->control('password', [
        'autocomplete' => 'new-password',
        'class' => 'form-control',
        'type' => 'password',
        'label' => __('user_pw'),
        'tabindex' => 3,
        'value' => ''
    ]) ?>
    </div>
<div class="form-group">
    <?= $this->Form->control('password_confirm', [
        'autocomplete' => 'new-password',
        'class' => 'form-control',
        'type' => 'password',
        'label' => __('user_pw_confirm'),
        'required' => true,
        'tabindex' => 4,
        'value' => ''
    ]) ?>
</div>
