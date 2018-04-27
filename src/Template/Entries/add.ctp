<?php

use Cake\Core\Configure;

//data passed as json model
$jsMeta = json_encode(
    [
        'action' => $this->request->getParam('action')
    ]
);
$jsEntry = '{}';
if ($this->request->getParam('action') === 'edit') {
    $jsEntry = json_encode(
        [
            'time' => $this->TimeH->dateToIso($posting->get('time'))
        ]
    );
}

// header subnav
$this->start('headerSubnavLeft');
$headerSubnavLeftTitle = $headerSubnavLeftTitle ?? null;
$headerSubnavLeftUrl = $headerSubnavLeftUrl ?? null;
echo $this->Layout->navbarBack($headerSubnavLeftUrl, $headerSubnavLeftTitle);
$this->end();
?>
<div
    class="entry <?= ($isAnswer) ? 'reply' : 'add' ?> <?= ($isInline) ? '' : 'add-not-inline' ?>">
    <div class="preview panel">
        <?=
        $this->Layout->panelHeading(
            [
                'first' => "<i class='fa fa-close-widget pointer btn-previewClose'> &nbsp;</i>",
                'middle' => __('preview')
            ],
            ['escape' => false]
        ) ?>
        <div class="panel-content"></div>
    </div>
    <!-- preview -->

    <div class="postingform panel">
        <?php
        $first = ($isInline) ? "<i class='fa fa-close-widget pointer btn-answeringClose'> &nbsp; </i>" : '';
        echo $this->Layout->panelHeading(
            [
                'first' => $first,
                'middle' => $titleForPage,
            ],
            ['pageHeading' => !$isInline, 'escape' => false]
        ); ?>
        <div id="markitup_upload">
            <div class="body"></div>
        </div>

        <div class="panel-content panel-form" style="position: relative;">
            <?php
            echo $this->Form->create($posting);
            echo $this->Posting->categorySelect($posting, $categories);
            $subject = (!empty($citeSubject)) ? $citeSubject : __('Subject');
            echo $this->Form->control(
                'subject',
                [
                    'maxlength' => $SaitoSettings->get('subject_maxlength'),
                    'label' => false,
                    'class' => 'js-subject subject',
                    'tabindex' => 2,
                    'div' => ['class' => 'required'],
                    'placeholder' => h($subject),
                    'required' => ($isAnswer) ? false : 'required'
                ]
            );
            echo $this->Html->div('postingform-subject-count', '');

            echo $this->Form->hidden('pid');
            echo $this->MarkitupEditor->getButtonSet('markItUp_' . $formId);
            echo $this->MarkitupEditor->editor(
                'text',
                [
                    'parser' => false,
                    'set' => 'default',
                    'skin' => 'macnemo',
                    'label' => false,
                    'tabindex' => 3,
                    'settings' => 'markitupSettings'
                ]
            );
            echo $this->Html->div(
                'postingform-eh',
                $this->Parser->editorHelp()
            );
            if (empty($citeText) === false) : ?>
                <div class="cite-container">
                    <?=
                    $this->Html->link(
                        Configure::read('Saito.Settings.quote_symbol') . ' ' . __('Cite'),
                        '#',
                        [
                            'data-text' => $this->Parser->citeText($citeText),
                            'class' => 'btn-cite label'
                        ]
                    );
                    ?>
                    <br/><br/>
                </div>
            <?php endif; ?>

            <div class="bp-threeColumn">
                <div class="left">
                    <?=
                    $this->Form->button(
                        __('submit_button'),
                        [
                            'id' => 'btn-submit',
                            'class' => 'btn btn-submit js-btn-submit',
                            'tabindex' => 4,
                            'type' => 'button'
                        ]
                    );
                    ?>
                    &nbsp;
                    <?=
                    $this->Html->link(
                        __('preview'),
                        '#',
                        ['class' => 'btn btn-preview', 'tabindex' => 5]
                    );
                    ?>
                </div>
                <div class="center">
                </div>
                <div class="right">
                    <?php
                    //= get additional profile info from plugins
                    $items = $SaitoEventManager->dispatch(
                        'Request.Saito.View.Posting.addForm',
                        [
                            'View' => $this
                        ]
                    );
                    foreach ($items as $item) {
                        echo $item;
                    }
                    ?>
                </div>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
        <!-- content -->
    </div>
    <!-- postingform -->
    <div class='js-data' data-entry='<?= $jsEntry ?>' data-meta='<?= $jsMeta ?>'></div>
</div> <!-- entry add/reply -->
