<?php
$this->Breadcrumbs->add(__('Settings'), false);
$tableHeadersHtml = $this->Setting->tableHeaders();

$this->start('settings');
echo $this->Setting->table(
    __('Deactivate Forum'),
    ['forum_disabled', 'forum_disabled_text'],
    $Settings
);

echo $this->Setting->table(
    __('Base Preferences'),
    ['forum_name', 'timezone'],
    $Settings
);

echo $this->Setting->table(
    __('Email'),
    ['forum_email', 'email_contact', 'email_register', 'email_system'],
    $Settings,
    ['sh' => 6]
);

echo $this->Setting->table(
    __('Moderation'),
    ['block_user_ui', 'store_ip', 'store_ip_anonymized'],
    $Settings
);

echo $this->Setting->table(
    __('Registration'),
    ['tos_enabled', 'tos_url'],
    $Settings
);

echo $this->Setting->table(
    __('Edit'),
    ['edit_period', 'edit_delay'],
    $Settings
);

echo $this->Setting->table(
    __('View'),
    [
        'topics_per_page',
        'thread_depth_indent',
        'autolink',
        'bbcode_img',
        'quote_symbol',
        'signature_separator',
        'subject_maxlength',
        'text_word_maxlength',
        'video_domains_allowed'
    ],
    $Settings
);

echo $this->Setting->table(
    __d('nondynamic', 'content_embed.t'),
    [
        'content_embed_active',
        'content_embed_media',
        'content_embed_text',
    ],
    $Settings
);

echo $this->Setting->table(
    __('Category Chooser'),
    ['category_chooser_global', 'category_chooser_user_override'],
    $Settings
);

echo $this->Setting->table(
    __('Debug'),
    ['stopwatch_get'],
    $Settings
);
$this->end('settings');
?>
<div id="settings_index" class="settings index">
    <div class="row">
        <div class="col-md-3 navbarsidelist">
            <nav class="nav nav-pills flex-column" style="position: sticky; top: 1rem;">
                <?php foreach ($this->Setting->getHeaders() as $key => $title) : ?>
                    <a href="#navHeaderAnchor<?= $key ?>" class="nav-link">
                        <?= $title ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
        <div class="col-md-9">
            <h1><?php echo __('Settings'); ?></h1>
            <?= $this->fetch('settings') ?>
        </div>
    </div>
</div>
<script>
    var $body = document.getElementsByTagName('body')[0];
    $body.setAttribute('data-spy', 'scroll');
    $body.setAttribute('data-target', '.navbarsidelist');
    delete $body;
</script>
