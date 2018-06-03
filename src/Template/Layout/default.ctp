<?= $this->element('layout/html_header') ?>
    <link href='//fonts.googleapis.com/css?family=Fenix' rel='stylesheet' type='text/css'>
    <link href="//fonts.googleapis.com/css?family=Cabin:400,400italic,500italic,500,600italic,600,700italic,700" rel="stylesheet" type="text/css">
    <?= $this->Html->css('stylesheets/static.css') ?>
    <script>
        (function (SaitoApp) {
            var css = 'theme';
            try {
                preset = localStorage.theme;
                if (preset && preset === 'night') {
                    css = 'night';
                }
            } catch (e) {
            }
            document.write('<link rel="stylesheet" type="text/css" href="' + SaitoApp.app.settings.webroot + 'Paz/css/' + css + '.css" />');
            SaitoApp.app.theme = {preset: css};
        })(SaitoApp);
    </script>
    <noscript>
        <?= $this->Html->css('Paz.stylesheets/theme.css') ?>
    </noscript>
</head>
<body class="l-body">
    <script>
        var _headerClosed = localStorage.headerClosed;
        if (_headerClosed === 'true') {
            $('body').addClass('headerClosed');
        }
    </script>
    <?php
    $action = $this->request->getParam('action');
    if (!$CurrentUser->isLoggedIn() && ($action !== 'login' && $action !== 'register')) {
        echo $this->element('users/login_modal');
    }
    ?>
    <div id="site">
        <header id="site-header">
            <div id="hero">
                <?php
                $homeLink = '<div id="hero-home-link">' . h($forumName) . '</div>';
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
            <div class="top-menu">
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
                            '/admin',
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
        /*
         * Navbar
         */
        $navLeft = $this->fetch('headerSubnavLeft');
        $navRight = $this->element('layout/header_subnav_right');

        $navCenter = '';
        if ($this->request->getParam('controller') !== 'Entries'
            || !in_array($this->request->getParam('action'), ['mix', 'view'])) {
            $navCenter = $this->fetch('headerSubnavCenter');
            if (empty($navCenter)) {
                $navCenter = $this->Layout->pageHeading($titleForPage);
            }
        }

        echo $this->Layout->heading(
            ['first' => $navLeft, 'middle' => $navCenter, 'last' => $navRight],
            ['id' => 'topnav', 'class' => 'navbar', 'escape' => false]
        );

        /*
         * Slidetabs
         */
        if (!empty($slidetabs)) {
            \Stopwatch\Lib\Stopwatch::start('Slidetabs');
            echo '<aside id="slidetabs">';
            foreach ($slidetabs as $slidetab) {
                echo $this->cell($slidetab);
            }
            echo '</aside>';
            \Stopwatch\Lib\Stopwatch::end('Slidetabs');
        }
        ?>

        <div id="content">
            <script type="text/javascript">
                if (!SaitoApp.request.isPreview) { $('#content').css('visibility', 'hidden'); }
            </script>
            <?php echo $this->fetch('content'); ?>
        </div>
        <?php
        /*
         * Navbar bottom
         */
        if ($showBottomNavigation ?? false) {
            echo '<div id="footer-pinned">';

            $navCenter = '<a href="#" class="js-scrollToTop btn-hf-center">' .
                $this->Layout->textWithIcon('', 'arrow-up') .
                '</a>';
            echo $this->Layout->heading(
                ['first' => $navLeft, 'middle' => $navCenter, 'last' => $navRight],
                ['id' => 'bottomnav', 'class' => 'navbar', 'escape' => false]
            );

            echo '</div>';
        }
        ?>
    </div>
    <?php
    if ($showDisclaimer ?? false) {
        echo $this->element('layout/disclaimer');
    }
    ?>
    <div id="saito-modal-dialog" class="modal fade"  tabindex="-1" role="dialog" aria-hidden="true"></div>
    <?= $this->element('layout/html_footer'); ?>
    <?= $this->Html->script('Paz.theme.js') ?>
</body>
</html>
