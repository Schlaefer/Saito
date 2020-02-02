<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <?= $this->Html->link(__('Forum'), '/', ['class' => 'navbar-brand']); ?>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-toggle">
<span class="navbar-toggler-icon"></span>
</button>
    <div class="collapse navbar-collapse" id="navbar-toggle">
        <ul class="navbar-nav">
            <li class="nav-item <?= preg_match('/\/admin$/', $this->request->getRequestTarget()) ? 'active' : '' ?>">
                <?= $this->Html->link(__('Overview'), '/admin/', ['class' => 'nav-link']) ?>
            </li>
            <li class="nav-item <?= stristr($this->request->getRequestTarget(), 'settings') ? 'active' : '' ?>">
                <?= $this->Html->link(__('Settings'), '/admin/settings/index', ['class' => 'nav-link']); ?>
            </li>
            <li class="nav-item dropdown <?= stristr($this->request->getRequestTarget(), 'users') ? 'active' : '' ?>">
                <?php
                echo $this->Html->link(
                    __('Users'),
                    '/admin/users/index',
                    [
                        'class' => 'nav-link dropdown-toggle',
                        'data-toggle' => 'dropdown',
                    ]
                );
                echo $this->Html->nestedList(
                    [
                        $this->Html->link(
                            __('Users'),
                            '/admin/users/index',
                            ['class' => 'dropdown-item']
                        ),
                        $this->Html->link(
                            __('user.block.history'),
                            '/admin/users/block',
                            ['class' => 'dropdown-item']
                        ),
                    ],
                    ['class' => 'dropdown-menu']
                );
                ?>
            </li>
            <li class="nav-item <?= stristr($this->request->getRequestTarget(), 'categories') ? 'active' : '' ?>">
                <?= $this->Html->link(__('Categories'), '/admin/categories/index', ['class' => 'nav-link']); ?>
            </li>
            <li class="nav-item <?= stristr($this->request->getRequestTarget(), 'smilies') ? 'active' : '' ?>">
                <?= $this->Html->link(__('Smilies'), '/admin/smilies/index', ['class' => 'nav-link']) ?>
            </li>
            <?php
            //= plugins
            $items = $SaitoEventManager->dispatch('Request.Saito.View.Admin.plugins');
            if ($items) {
                $dropdown = $this->Html->link(
                    __('Plugins'),
                    '#',
                    [
                        'class' => 'nav-link dropdown-toggle',
                        'data-toggle' => 'dropdown',
                    ]
                );

                $plugins = [];
                $plugins[] = $this->Html->link(
                    __('Plugins'),
                    '/admin/plugins',
                    ['class' => 'dropdown-item']
                );
                $plugins[] = '<div class="dropdown-divider"></div>';
                foreach ($items as $item) {
                    $plugins[] = $this->Html->link(
                        $item['title'],
                        $item['url'],
                        ['class' => 'dropdown-item']
                    );
                }
                $dropdown .= $this->Html->nestedList($plugins, ['class' => 'dropdown-menu']);

                $active = stristr($this->request->getRequestTarget(), 'plugin') ? ' active' : '';
                $dropdown = $this->Html->tag('li', $dropdown, ['class' => 'dropdown' . $active]);
                echo $dropdown;
            }
            ?>
        </ul>
    </div>
</nav>
