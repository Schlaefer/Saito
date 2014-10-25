<?php
	//data passed as json model
	$_jsMeta = json_encode([
		'action' => $this->request->action
	]);
	$_jsEntry = '{}';
	if ($this->request->action === 'edit') {
		$_jsEntry = json_encode([
			'time' => $this->TimeH->mysqlTimestampToIso($this->request->data['Entry']['time'])
		]);
	}

	// header subnav
	$this->start('headerSubnavLeft');
  SDV($headerSubnavLeftTitle, null);
  SDV($headerSubnavLeftUrl, null);
  echo $this->Layout->navbarBack($headerSubnavLeftUrl, $headerSubnavLeftTitle);
	$this->end();
?>
	<div class="entry <?= ($is_answer) ? 'reply' : 'add' ?> <?= ($is_inline) ? '' : 'add-not-inline' ?>">
	<div class="preview panel">
		<?=
			$this->Layout->panelHeading([
					'first' => "<i class='fa fa-close-widget pointer btn-previewClose'> &nbsp;</i>",
					'middle' => __('preview')
			], ['escape' => false]) ?>
		<div class="panel-content"></div>
	</div>
	<!-- preview -->

	<div class="postingform panel">
		<?php
		 $_first = ($is_inline) ? "<i class='fa fa-close-widget pointer btn-answeringClose'> &nbsp; </i>" : '';
			echo $this->Layout->panelHeading([
							'first' => $_first,
							'middle' => $title_for_page,
					],
					['pageHeading' => !$is_inline, 'escape' => false]);?>
		<div id="markitup_upload">
			<div class="body"></div>
		</div>
		<div id='markitup_media' style="display: none; overflow: hidden;"></div>

    <div class="panel-content panel-form">
      <?php
        echo $this->Form->create('Entry');
        echo $this->EntryH->categorySelect($this->request->data, $categories);
        echo $this->Form->input('subject', [
            'maxlength' => Configure::read('Saito.Settings.subject_maxlength'),
            'label' => false,
            'class' => 'js-subject subject',
            'tabindex' => 2,
            'error' => [
              'notEmpty' => __('error_subject_empty'),
              'maxLength' => __('error_subject_max_length')
            ],
            'div' => ['class' => 'required'],
            'placeholder' => (!empty($citeSubject)) ? $citeSubject : __('Subject'),
            'required' => ($is_answer) ? false : "required"
          ]
        );
        echo $this->Form->hidden('pid');
        echo $this->MarkitupEditor->getButtonSet('markItUp_' . $form_id);
        echo $this->MarkitupEditor->editor(
          'text',
          [
            'class' => 'shp',
            'data-shpid' => 3,
            'parser' => false,
            'set' => 'default',
            'skin' => 'macnemo',
            'label' => false,
            'tabindex' => 3,
            'settings' => 'markitupSettings'
          ]
        );
        if (empty($citeText) === false) :
          ?>
					<div class="cite-container">
						<?=
							$this->Html->link(
								Configure::read('Saito.Settings.quote_symbol')
								. ' ' . __('Cite'),
								'#',
								[
									'data-text' => $this->Bbcode->citeText($citeText),
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
									'id'         => 'btn-submit',
									'class'      => 'btn btn-submit js-btn-submit',
									'tabindex'   => 4,
									'type'       => 'button'
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
						<div class="checkbox">
							<?php
								echo $this->Form->checkbox(
									'Event.1.event_type_id',
									['checked' => isset($notis[0]) && $notis[0]]
								);
								echo $this->Form->label(
									'Event.1.event_type_id',
									__('Notify on reply')
								);
							?>
						</div>
						<div class="checkbox">
							<?php
								echo $this->Form->checkbox(
									'Event.2.event_type_id',
									[
										'checked' => isset($notis[1]) && $notis[1],
									]
								);
								echo $this->Form->label(
									'Event.2.event_type_id',
									__('Notify on thread replies')
								);
							?>
						</div>
					</div>
					<div class="right">
						<?php
							//= get additional profile info from plugins
							$items = SaitoEventManager::getInstance()->dispatch(
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
	<div class='js-data' data-entry='<?= $_jsEntry ?>' data-meta='<?= $_jsMeta ?>'></div>
	</div> <!-- entry add/reply -->
