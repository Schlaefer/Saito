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
            // used for countdown on edit button (edit time remaining)
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
    <div class="preview-wrapper" style="display: none;"></div>

    <div class="postingform card">
        <?php
        // close form button
        if ($isInline) {
            $closeInline = $this->Form->button(
                $this->Layout->textWithIcon('', 'close-widget'),
                ['class' => 'js-btnAnsweringClose close', 'type' => 'button']
            );
            $heading = $this->Layout->panelHeading(
                ['first' => $closeInline ?? '', 'middle' => $titleForPage],
                ['pageHeading' => !$isInline, 'escape' => false]
            );
            echo $this->Html->div('card-header', $heading);
        }
        ?>

        <div class="card-body" style="position: relative;">
            <?php
            echo $this->Form->create($posting, ['id' => 'EntryAddForm', 'autocomplete' => 'off']);
            echo $this->Posting->categorySelect($posting, $categories);

            $subject = (!empty($citeSubject)) ? $citeSubject : __('Subject');
            $subjectInput = $this->Form->control(
                'subject',
                [
                    'maxlength' => $SaitoSettings->get('subject_maxlength'),
                    'label' => false,
                    'class' => 'js-subject postingform-subject form-control',
                    'tabindex' => 2,
                    'div' => ['class' => 'required'],
                    'placeholder' => $subject,
                    'required' => ($isAnswer) ? false : 'required'
                ]
            );

            $progress = $this->Html->div('js-progress progress-bar', '', ['role' => 'progressbar']);
            $subjectInput .= $this->Html->div('progress postingform-subject-progress', $progress);

            $subjectInput .= $this->Html->div('postingform-subject-count', '');
            echo $this->Html->div('postingform-subject-wrapper form-group', $subjectInput);

            echo $this->Form->hidden('pid');

            echo $this->Parser->editor('text');

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
                $previewButtton = $this->Html->tag(
                    'button',
                    __('preview'),
                    [
                        'class' => 'js-btnPreview btn btn-secondary',
                        'tabindex' => 5,
                        'type' => 'button'
                    ]
                );

                $first = $this->Html->div('form-group', $submitButton . $previewButtton);
                echo $this->Html->div('first', $first);

                // middle
                $middle = '';
                // citation button
                if (empty($citeText) === false) {
                    $citeLink = $this->Form->button(
                        Configure::read('Saito.Settings.quote_symbol') . ' ' . __('Cite'),
                        [
                            'class' => 'btn btn-link js-btnCite label',
                            // Encode so that " in quoted text doesn't break out of HTML Attribute.
                            'data-text' => htmlspecialchars($this->Parser->citeText($citeText)),
                            'type' => 'button',
                        ]
                    );
                    $citeLink = $this->Html->div('form-group', $citeLink);
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
