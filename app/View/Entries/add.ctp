<?php
	// header subnav
	$this->start('headerSubnavLeft');
	echo $this->Html->link(
		'<i class="icon-arrow-left"></i>&nbsp; ' . $headerSubnavLeftTitle,
		$headerSubnavLeftUrl,
		array('class' => 'textlink', 'escape' => false)
	);
	$this->end();
	$posting_type = ($is_answer) ? 'reply' : 'add';
?>
	<div id="entry_<?= $posting_type ?>" class="entry <?= $posting_type ?>">

	<div class="preview">
		<div class="l-box-header box-header">
			<div>
				<div class="c_first_child">
					<i class='icon-close-widget icon-large pointer btn-icon-close btn-previewClose'>
						&nbsp;</i>
				</div>
				<div>
					<h2>
						<?php echo __('preview') ?>
					</h2>
				</div>
				<div class="c_last_child">
					&nbsp;
				</div>
			</div>
		</div>
		<!-- header -->
		<div class="content"></div>
	</div>
	<!-- preview -->

	<div class="postingform">
		<div class="l-box-header box-header">
			<div>
				<div class="c_first_child">
					<?php if ($this->request->is('ajax')) : ?>
						<i class='icon-close-widget icon-large btn-icon-close pointer btn-answeringClose'>
							&nbsp;
						</i>
					<?php endif; ?>
				</div>
				<div>
					<h2>
						<?= $form_title; ?>
					</h2>
				</div>
				<div class="c_last_child">&nbsp;</div>
			</div>
		</div>

		<div id="markitup_upload">
			<div class="body"></div>
		</div>
		<div id='markitup_media' style="display: none; overflow: hidden;"></div>

		<div class="content">
					<?php echo  $this->Form->create('Entry'); ?>
			<div class="l-postingform_main">
				<?php echo $this->EntryH->getCategorySelectForEntry(
					$categories,
					$this->request->data
				); ?>
				<?=
					$this->Form->input(
						'subject',
						[
							'maxlength' => Configure::read(
								'Saito.Settings.subject_maxlength'
							),
							'label'       => false,
							'tabindex'    => 2,
							'error'       => [
								'notEmpty' => __('error_subject_empty'),
								'maxLength' => __('error_subject_max_length')
							],
							'div'         => ['class' => 'required'],
							'placeholder' => (!empty($citeSubject)) ? $citeSubject : __('Subject'),
							'required'		=> ($is_answer) ? false : "required"
						]
					);
				?>
				<?= $this->Form->hidden('pid'); ?>
				<?php
					echo $this->MarkitupEditor->getButtonSet(
						'markItUp_' . $form_id
					);
					echo $this->MarkitupEditor->editor(
						'text',
						[
							'parser'   => false,
							'set'      => 'default',
							'skin'     => 'macnemo',
							'label'    => false,
							'tabindex' => 3,
							'settings' => 'markitupSettings'
						]
					);
				?>
				<?php
					// add original posting contents
					if (empty($citeText) === false) :
						?>
						<div
								id="<?= "btn_insert_original_text_{$form_id}"; ?>">
							<?php
								echo $this->Html->scriptBlock(
									"var quote_{$form_id} = " . json_encode(
										$this->Bbcode->citeText($this->getVar('citeText'))
									) . "; ",
									['inline' => 'true']
								);
								// empty the textarea
								echo $this->Html->scriptBlock(
									"$('#markItUp_{$form_id} #EntryText').val('')",
									['inline' => 'true']
								);
								echo $this->Html->link(
									Configure::read('Saito.Settings.quote_symbol') . ' ' . __(
										'Cite'
									),
									'#',
									[
										'onclick' => "$('#markItUp_{$form_id} #EntryText').val(quote_{$form_id} + '" . '\n\n' . "' + $('#markItUp_{$form_id} #EntryText').val());"
										. "$('#btn_insert_original_text_{$form_id}').slideToggle();"
										. "$('#markItUp_{$form_id} #EntryText').focus();"
										. "return false;",
										'class'   => 'label'
									]
								);
							?>
						</div>
						<br/>
					<?php endif; //add original posting contents ?>

				<div class="bp-threeColumn">
					<div class="left">
						<?php
							# @bogus^2
							if (!$this->request->is(
										'ajax'
									) || (isset($lastAction) && ($lastAction === 'mix' || $lastAction === 'view' || $lastAction === 'add'))
							) {
								$inline = false;
							} else {
								$inline = true;
							}

							echo $this->Form->submit(
								__('submit_button'),
								[
									'id'         => 'btn-submit',
									'class'      => 'btn btn-submit' . (($inline) ? ' js-inlined' : ''),
									'tabindex'   => 4,
									'ondblclick' => 'return false;',
									'onclick'    => "
									if ({$inline}) return false;
									if (typeof this.validity === 'object' &&
										this.form.checkValidity() === false) {
										return true;
									} else {
											this.disabled = true;
											this.form.submit();
									}
									return false;
									"
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
								if (Configure::read(
											'Saito.Settings.flattr_enabled'
										) == true && $CurrentUser['flattr_uid'] == true
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
	</div> <!-- entry add/reply -->

<?php if ($this->request->action === 'edit'): ?>
	<span id="submit-countdown" class="countdown" style="display: none;"></span>
	<?php
		echo $this->Html->script('lib/countdown/jquery.countdown.min');
		$sbl = __('submit_button');
		$st  = (Configure::read('Saito.Settings.edit_period') * 60 ) - (time() - (strtotime($this->request->data['Entry']['time'])));
		$this->Js->buffer(<<<EOF
	$('#submit-countdown').countdown({
		until: +$st,
		compact: true,
		format: 'MS',
		onTick: function(periods) {
				if (periods[5] > 1 || (periods[5] == 1 && periods[6] > 30)) {
					periods[5] = periods[5] + 1;
					$('#btn-submit').attr('value', '$sbl' + ' (' + periods[5] + ' min)');
				} else if (periods[5] == 1) {
					$('#btn-submit').attr('value', '$sbl' + ' (' + periods[5] + ' min ' + periods[6] + ' s)');
				} else {
					$('#btn-submit').attr('value', '$sbl' + ' (' + periods[6] + ' s)');
				}
		},
		onExpiry: function() {
				$('#btn-submit').attr('disabled', 'disabled');
			}
	});
EOF
			);
endif; ?>