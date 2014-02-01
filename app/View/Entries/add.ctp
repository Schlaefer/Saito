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
	echo $this->Html->link(
		'<i class="fa fa-arrow-left"></i>&nbsp; ' . $headerSubnavLeftTitle,
		$headerSubnavLeftUrl,
		array('class' => 'textlink', 'escape' => false)
	);
	$this->end();
?>
	<div class="entry <?= ($is_answer) ? 'reply' : 'add' ?> <?= ($is_inline) ? '' : 'add-not-inline' ?>">
	<div class="preview">
		<?=
			$this->Layout->panelHeading([
					'first' => "<i class='fa fa-close-widget fa-lg pointer btn-previewClose'> &nbsp;</i>",
					'middle' => __('preview')
			]) ?>
		<div class="panel-content"></div>
	</div>
	<!-- preview -->

	<div class="postingform panel-form">
		<?php
		 $_first = ($this->request->is('ajax')) ? "<i class='fa fa-close-widget fa-lg pointer btn-answeringClose'> &nbsp; </i>" : '';
			echo $this->Layout->panelHeading([
					'first' => $_first,
					'middle' => $title_for_page,
					['class' => (!$is_inline) ? 'pageTitle' : '']
			]) ?>
		<div id="markitup_upload">
			<div class="body"></div>
		</div>
		<div id='markitup_media' style="display: none; overflow: hidden;"></div>

		<div class="panel-content">
					<?php echo  $this->Form->create('Entry'); ?>
			<div class="l-postingform_main">
				<?php
					echo $this->EntryH->categorySelect($this->request->data, $categories);
					echo $this->Form->input(
						'subject',
						[
							'maxlength' => Configure::read('Saito.Settings.subject_maxlength'),
							'label' => false,
							'class' => 'inp-subject',
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
					echo $this->MarkitupEditor->getButtonSet(
						'markItUp_' . $form_id
					);
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
				<?php if (empty($citeText) === false) : ?>
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
						<div class="checkbox">
							<?php
								echo $this->Form->checkbox('nsfw');
								echo $this->Form->label('nsfw', __('entry_nsfw_title'));
							?>
						</div>
						<div class="checkbox">
							<?php
								// ### flattr checkbox start
								if (Configure::read('Saito.Settings.flattr_enabled') == true &&
										$CurrentUser['flattr_uid'] == true
								) :
									echo $this->Form->checkbox('flattr');
									echo $this->Form->label(
										'flattr',
										__('entry_flattr_this_posting')
									);

									// ### JS code for dynamicaly switching the checkbox accordingly to category
									$code_insert = "
									var elements = [" . implode(
												",",
												$category_flattr
											) . "];
									if ( elements.indexOf(parseInt(data)) >= 0 ) {
											$('#EntryFlattr').attr('checked', true);
										} else {
											$('#EntryFlattr').attr('checked', false);
										}";

									if ($CurrentUser['flattr_allow_posting'] == false) {
										$code_insert .= "$('#EntryFlattr').attr('checked', false);";
									}

									if ($this->request->is('ajax')) {
										// if it an answer
										$code = "$(document).ready(function (){
										var data = " . $this->request->data['Entry']['category'] . ";
										$code_insert
									});";
									} else {
										// if it a new posting
										$code = "$(document).ready(function () { $('#EntryCategory').change(function() {
										var data = $(this).val();
										$code_insert
									})});";
									}
									echo $this->Html->scriptBlock($code);
								endif;
								// ### flattr checkbox end
							?>
						</div>
					</div>
				</div>
			</div>
			<?php echo $this->Form->end(); ?>
		</div>
		<!-- content -->
	</div>
	<!-- postingform -->
	<div class='js-data' data-entry='<?= $_jsEntry ?>' data-meta='<?= $_jsMeta ?>'></div>
	</div> <!-- entry add/reply -->
