<?= $this->Html->docType('html5') . "\n"; ?>
<html>
<head>
    <?= $this->element('layout/html_header') ?>
    <?= $this->Html->css('stylesheets/static.css') ?>

    <?= $this->fetch('theme_head') ?>
</head>
<body>
    <div id="site">
        <?php
        /**
         * Header
         */
        echo $this->fetch('theme_header');

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
            ['id' => 'site-navigation-top', 'escape' => false]
        );

        /*
         * Slidetabs
         */
        if (!empty($slidetabs)) {
            \Stopwatch\Lib\Stopwatch::start('Slidetabs');
            // made visible by frontend if ready
            echo '<aside id="slidetabs" style="visibility: hidden;">';
            foreach ($slidetabs as $slidetab) {
                echo $this->cell($slidetab, ['CurrentUser' => $CurrentUser]);
            }
            echo '</aside>';
            \Stopwatch\Lib\Stopwatch::end('Slidetabs');
        }
        ?>

        <?php
        /**
         * Content
         */
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
                ['id' => 'site-navigation-bottom', 'escape' => false]
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
    <?= $this->fetch('theme_footer'); ?>
</body>
</html>
