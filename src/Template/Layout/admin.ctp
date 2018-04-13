<?= $this->Html->docType('html5'); ?>
<html>
<head>
    <title><?= h($titleForLayout) ?></title>
    <?php
    echo $this->Html->charset();
    echo $this->element('layout/script_tags', ['require' => false]);
    echo $this->Html->css(
        ['stylesheets/static.css', 'stylesheets/admin.css']
    );
    echo $this->Html->script('../dist/bootstrap.min');
    ?>
    <style type="text/css">
        div.submit {
            /*display: inline-block; margin: 0 1em;*/
        }

        .modal-footer form {
            margin: 0;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="navbar">
        <div class="navbar-inner">
            <div class="container" style="width: auto;">
                <?=
                $this->Html->link(
                    __('Forum'),
                    '/',
                    ['class' => 'brand']
                );
                ?>
                <?php
                $jqueryVsBootstrapFix = ['onclick' => "$('.dropdown').removeClass('dropdown');"];
                ?>
                <ul class="nav">
                    <li class="<?= preg_match('/\/admin$/', $this->request->here) ? 'active' : '' ?>">
                        <?= $this->Html->link(__('Overview'), '/admin/') ?>
                    </li>
                    <li class="<?= stristr($this->request->here, 'settings') ? 'active' : '' ?>">
                        <?php echo $this->Html->link(__('Settings'), '/admin/settings/index'); ?>
                    </li>
                    <li class="dropdown <?= stristr($this->request->here, 'users') ? 'active' : '' ?>">
                        <?php
                        echo $this->Html->link(
                            __('Users') . ' ▾',
                            '/admin/users/index',
                            [
                                'class' => 'drowdown-toggle',
                                'data-toggle' => 'dropdown'
                            ]
                        );
                        echo $this->Html->nestedList(
                            [
                                $this->Html->link(
                                    __('Users'),
                                    '/admin/users/index',
                                    $jqueryVsBootstrapFix
                                ),
                                $this->Html->link(
                                    __('user.block.history'),
                                    '/admin/users/block',
                                    $jqueryVsBootstrapFix
                                )
                            ],
                            ['class' => 'dropdown-menu']
                        );
                        ?>
                    </li>
                    <li class="<?= stristr($this->request->here, 'categories') ? 'active' : '' ?>">
                        <?php echo $this->Html->link(__('Categories'), '/admin/categories/index'); ?>
                    </li>
                    <li class="<?= stristr($this->request->here, 'smilies') ? 'active' : '' ?>">
                        <?= $this->Html->link(__('Smilies'), '/admin/smilies/index') ?>
                    </li>
                    <?php
                    //= plugins
                    $items = $SaitoEventManager->dispatch('Request.Saito.View.Admin.plugins');
                    if ($items) {
                        $dropdown = $this->Html->link(
                            __('Plugins') . ' ▾',
                            '/admin/plugins',
                            [
                                'class' => 'drowdown-toggle',
                                'data-toggle' => 'dropdown'
                            ]
                        );
                        foreach ($items as $item) {
                            $plugins[] = $this->Html->link(
                                $item['title'],
                                $item['url'],
                                $jqueryVsBootstrapFix
                            );
                        }
                        $dropdown .= $this->Html->nestedList($plugins, ['class' => 'dropdown-menu']);

                        $active = stristr($this->request->here, 'plugin') ? ' active' : '';
                        $dropdown = $this->Html->tag('li', $dropdown, ['class' => 'dropdown' . $active]);
                        echo $dropdown;
                    }
                    ?>
                </ul>
                <ul class="nav pull-right">
                    <li class="divider-vertical"></li>
                    <li>
                        <a href="<?= Cake\Core\Configure::read('Saito.saitoHomepage') ?>">
                            Saito
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="span1">&nbsp;</div>
        <div class="span10">
            <?php
            echo $this->element('Flash/render');
            echo $this->Html->getCrumbs(' > ');
            echo $this->fetch('content');
            ?>
        </div>
        <div class="span1">&nbsp;</div>
    </div>
</div>
<?php
echo $this->fetch('script');
echo $this->Html->script(
    ['lib/datatables-bootstrap/datatables-bootstrap.js']
);
echo $this->element('layout/debug_footer');
?>
</body>
</html>
