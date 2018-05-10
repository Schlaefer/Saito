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
<div class="entry <?= ($isAnswer) ? 'reply' : 'add' ?> <?= ($isInline) ? '' : 'add-not-inline' ?>">
    <?php
    $closeButton = $this->Form->button(
        $this->Layout->textWithIcon('', 'close-widget'),
        ['class' => 'js-btnPreviewClose close float-left', 'type' => 'button']
    );
    $heading = $this->Layout->panelHeading(
        ['first' => $closeButton, 'middle' => __('preview')],
        ['escape' => false]
    );

    $content = $this->Html->div('panel-content', '');

    echo $this->Html->div('preview panel', $heading . $content);
    ?>
    <!-- preview -->

    <div class="postingform panel">
        <?php
        // close form button
        if ($isInline) {
            $closeButton = $this->Form->button(
                $this->Layout->textWithIcon('', 'close-widget'),
                ['class' => 'js-btnAnsweringClose close float-left', 'type' => 'button']
            );
        }

        echo $this->Layout->panelHeading(
            ['first' => $closeButton ?? '', 'middle' => $titleForPage],
            ['pageHeading' => !$isInline, 'escape' => false]
        );
        ?>
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
                    'placeholder' => $subject,
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

            ?>
            <div class="postingform-buttons">
                <?php
                // first
                $submitButton = $this->Form->button(
                    __('submit_button'),
                    [
                        'id' => 'btn-primary',
                        'class' => 'btn btn-primary js-btn-primary',
                        'tabindex' => 4,
                        'type' => 'button'
                    ]
                );
                $previewButtton = $this->Html->link(
                    __('preview'),
                    '#',
                    ['class' => 'btn btn-preview', 'tabindex' => 5]
                );

                $first = $submitButton . $previewButtton;
                echo $this->Html->div('first', $first);

                // middle
                $middle = '';
                // citation button
                if (empty($citeText) === false) {
                    $citeLink = $this->Html->link(
                        Configure::read('Saito.Settings.quote_symbol') . ' ' . __('Cite'),
                        '#',
                        [
                            'data-text' => $this->Parser->citeText($citeText),
                            'class' => 'btn js-btnCite label'
                        ]
                    );
                    $middle .= $citeLink;
                }

                echo $this->Html->div('middle', $middle);

                // last
                $last = '';
                // get additional profile info from plugins
                $items = $SaitoEventManager->dispatch(
                    'Request.Saito.View.Posting.addForm',
                    ['View' => $this]
                );
                if (!empty($items)) {
                    foreach ($items as $item) {
                        $last .= $item;
                    }
                }

                echo $this->Html->div('last', $last);
                ?>
            </div>
            <?php
            echo $this->Html->div('postingform-info', $this->Parser->editorHelp());
            echo $this->Form->end();
            ?>
        </div>
        <!-- content -->
    </div>
    <!-- postingform -->
    <div class='js-data' data-entry='<?= $jsEntry ?>' data-meta='<?= $jsMeta ?>'></div>
</div> <!-- entry add/reply -->
