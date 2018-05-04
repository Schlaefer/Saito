<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack('/bookmarks/index/#' . $posting->get('id'));
$this->end();
?>
<div class="panel">
    <?=
    $this->Layout->panelHeading(
        __('Edit Bookmark'),
        ['pageHeading' => true]
    )
    ?>
    <div class="panel-content">
        <?= $this->element('/entry/view_content', ['entry' => $posting]); ?>
    </div>
    <div class="panel-footer panel-form">
        <?php
        echo $this->Form->create($bookmark);
        echo $this->Html->div(
            'input textarea',
            $this->Form->textarea(
                'comment',
                [
                    'maxlength' => '255',
                    'columns' => '80',
                    'rows' => '3',
                    'placeholder' => __('Enter your comment here'),
                ]
            )
        );
        echo $this->Form->submit(
            __('btn-comment-title'),
            ['class' => 'btn btn-primary']
        );
        echo $this->Form->end();
        ?>
    </div>
</div>
