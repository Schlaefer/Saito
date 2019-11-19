<?php

// extends src/Template/Layout/_default.ctp
$this->extend('_default');

$this->start('theme_head');
?>
    <link rel="icon" type="image/vnd.microsoft.icon" href="/favicon.ico"/>

    <script>
        (function (SaitoApp) {
            var theme = {
                css: '<?= $this->Url->assetUrl('Bota.css/theme.css') ?>',
                name: 'theme',
            }

            try {
                preset = localStorage.theme;
                if (preset && preset === 'night') {
                    theme.css = '<?= $this->Url->assetUrl('Bota.css/night.css') ?>';
                    theme.name = 'night';
                }
            } catch (e) {
            }
            document.write('<link rel="stylesheet" type="text/css" href="' + theme.css + '" />');
            SaitoApp.app.theme = {preset: theme.name};
        })(SaitoApp);
    </script>
    <noscript>
        <?= $this->Html->css('Bota.theme.css') ?>
    </noscript>

<?php
$this->end();

$this->start('theme_header');
?>
    <script>
        var _headerClosed = localStorage.headerClosed;
        if (_headerClosed === 'true') {
            $('body').addClass('headerClosed');
        }
    </script>
    <header id="header">
        <div id="header-hero">
            <?php
            $homeLink = '<div id="hero-homeLink">' . h($forumName) . '</div>';
            $options = ['id' => 'btn_header_logo'];
            // note: don't change the next line or you may break Mark-As-Read
            echo $this->Html->link(
                $homeLink,
                '/' . (isset($markAsRead) ? '?mar' : ''),
                $options + ['escape' => false]
            );
            ?>
            <button id="js-top-menu-open" class="btn btn-link">
                <i class="fa fa-plus-square-o"></i>
            </button>
        </div>
        <div id="header-menu">
            <div class="first">
                <?= $this->element('layout/header_login') ?>
            </div>
            <div class="middle">
                <?php
                // link to search
                echo $this->Html->link(
                    $this->Layout->textWithIcon(h(__('Search')), 'search'),
                    '/searches/simple',
                    ['class' => 'btn btn-link', 'escape' => false]
                );

                // link to admin-backend
                if ($CurrentUser->permission('saito.core.admin.backend')) {
                    echo $this->Html->link(
                        $this->Layout->textWithIcon(h(__('ial.aa')), 'wrench'),
                        '/admin/',
                        ['class' => 'btn btn-link', 'escape' => false]
                    );
                }
                ?>
            </div>
            <div class="last">
                <button id="shp-show" class="btn btn-link shp-show-btn">
                    <i class="fa fa-question-circle"></i>
                </button>
                <button id="js-themeSwitcher" class="btn btn-link" style="min-width: 3.1em">
                </button>
                <button id="js-top-menu-close" class="btn btn-link">
                    <i class="fa fa-minus-square-o"></i>
                </button>
            </div>
        </div>
    </header>
<?php
$this->end();

$this->start('theme_footer');
    echo $this->Html->script('Bota.theme.js', ['async' => 'true']);
$this->end();

echo $this->fetch('content');
