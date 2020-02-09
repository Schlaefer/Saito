<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 * @var \App\View\AppView $this
 */
use Stopwatch\Lib\Stopwatch;

Stopwatch::start('category-chooser.ctp'); ?>
<script type="x-template/underscore" id="tpl-categoryChooser">
<div id="category-chooser" class="panel panel-form">
    <?php
    echo $this->Form->create(
        null,
        [
            'url' => [
                'controller' => 'users',
                'action' => 'setcategory',
            ],
            'style' => 'clear: none;',
        ]
    );
    ?>

    <ul class="category-chooser-ul">
        <li class="category-chooser-li">
            <?php
            /* For performance reasons we generate the html manually */
            /*
            echo $this->Html->link(__('All'), '/users/setcategory/all')
                */
            ?>
            <a href="<?= $this->request->getAttribute('webroot') ?>users/setcategory/all">
                <?= __('All Categories') ?>
            </a>

        </li>
        <?php foreach ($categoryChooser as $key => $title) : ?>
            <li class="category-chooser-li">
                <?php
                echo $this->Form->checkbox(
                    'CatChooser.' . $key,
                    [
                        'onclick' => "$('#cb-category-chooser-all').removeAttr('checked')",
                        'checked' => isset($categoryChooserChecked[$key]),
                        'value' => 1,
                    ]
                );

                /* For performance reasons we generate the html manually */
                /*
                echo $this->Html->link($title, '/users/setcategory/' . $key)
                    *
                    */
                ?>
                <a href="<?= $this->request->getAttribute('webroot') ?>users/setcategory/<?= $key; ?>">
                    <?php echo $title; ?>
                </a>

            </li>
        <?php endforeach; ?>
    </ul>
    <p>
        <?php echo __('category_chooser_context_exp'); ?>
    </p>
    <?php
    echo $this->Form->submit(
        __('Apply'),
        ['class' => 'btn btn-primary']
    );
    echo $this->Form->end();
    ?>
</div>
</script>
<?php Stopwatch::end('category-chooser.ctp'); ?>
