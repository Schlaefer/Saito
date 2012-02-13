<?
// new entries have no id (i.e. no reply an no edit), so wie set a filler var
if ( !isset($this->request->data['Entry']['id']) ) {
	$this->request->data['Entry']['id'] = 'foo';
}

// cite entry text if necessary
if ( $this->getVar('citeText') ) {
	$citeText =  $this->Bbcode->citeText($this->getVar('citeText'));
}

//* set cursor to category or subject field after load ###
if ( $this->getVar('isAjax') ) :
	echo $this->Html->scriptBlock('$(document).ready(function() {$("#EntrySubject").select();});');
else :
	echo $this->Html->scriptBlock('$(document).ready(function() {$("#EntryCategory").focus();});');
endif;
?>
<div id ="entry_<?= ($this->getVar('isAjax')) ? 'reply' : 'add'; ?>" class="entry <?= ($this->getVar('isAjax')) ? 'reply' : 'add'; ?>">
	<div id="preview_<?php echo $this->request->data['Entry']['id'] ?>" class="preview">
		<div class="c_header_1">
			<div>
				<div>
					<?
					$js_r = $this->Js->get('#preview_' . $this->request->data['Entry']['id'])->effect('slideToggle',
									array( 'speed' => 'fast' ));
					$this->Js->get('#btn_preview_close_' . $this->request->data['Entry']['id'])->event('click',
							$js_r);
					?>
					<div id="btn_preview_close_<?php echo $this->request->data['Entry']['id']; ?>" class='btn_close'></div>
				</div>
				<div>
					<h2>
<?= __('preview') ?>
					</h2>
				</div>
				<div class="c_last_child">
					&nbsp;
				</div>
			</div>
		</div><!-- header -->

		<div class="content">
			<div id="spinner_preview_<?php echo $this->request->data['Entry']['id']; ?>" class="spinner"></div>
			<div id="preview_slider_<?php echo $this->request->data['Entry']['id']; ?>">
			</div>
		</div> <!-- content -->
	</div> <!-- preview -->
	<div class="postingform">
		<div class="c_header_1">
			<div>
				<div>
<? if ( $this->getVar('isAjax') ) : ?>
						<div id="btn_close_<?php echo $this->request->data['Entry']['id'] ?>" class='btn_close' onclick="entries_add_toggle(<?php echo $this->request->data['Entry']['id'] ?>); return false;"></div>
							<?
							?>
<? endif; ?>
				</div>
				<div>
					<h2>
<?= $form_title; ?>
					</h2>
				</div>
				<div class="c_last_child">
				</div>
			</div>
		</div>

		<div id='markitup_media' style="display: none; overflow: hidden;">
			<?=
			$this->Form->create(FALSE,
					array(
					'url' => '#',
					'style' => 'width: 100%;' ));
			?>
			<?=
			$this->Form->label(
					'media', 'Bitte Verweis oder Code zum Einbinden angeben:',
					array(
					'class' => 'c_markitup_label',
					)
			);
			?>
			<?=
			$this->Form->textarea('media',
					array(
					'id' => 'markitup_media_txta',
					'class' => 'c_markitup_popup_txta',
					'rows' => '6',
					'columns' => '20',
			));
			?>
			<div class="clearfix"></div>
<?=
$this->Form->submit(__('Einfügen'),
		array( // @lo
		'style' => 'float: right;',
		'class' => 'btn_submit',
		'id' => 'markitup_media_btn',
));
?>
					<?= $this->Form->end(); ?>
			<div class="clearfix"></div>
			<br/>
			<div id="markitup_media_message" class="flash error" style="display: none;">
					Es wurde kein Video erkannt.<!-- @lo -->
			</div>
		</div>

		<div class="content">
					<?= $this->Form->create('Entry'); ?>
			<div class="bp_container">
					<?php echo $this->EntryH->getCategorySelectForEntry($categories,
							$this->request->data); ?>
				<div class="postingform_main">
					<?=
					$this->Form->input(
							'subject',
							array(
							'maxlength' => Configure::read('Saito.Settings.subject_maxlength'),
							'label' => false,
							'tabindex' => 2,
							'error' => array(
									'notEmpty' => __('error_subject_empty'),
							),
							'div' => array( 'class' => 'requiered' ),
							)
					);
					?>
				</div>
						<?php 
							echo $this->Form->hidden('pid');
						?>
				<div class="postingform_main">
						<?php
						echo $this->EntryH->generateMarkItUpEditorButtonSet('markItUp_' . $this->request->data['Entry']['id']);
						echo $this->Markitup->editor(
								'text',
								array( 'set' => 'macnemo', 'skin' => 'macnemo', 'label' => false, 'tabindex' => 3, 'settings' => 'markitupSettings' ));
						?>
				</div> <!-- postingform_main -->
				<div class="postingform_right">

					<?php
					// add original posting contents
					if ( isset($citeText) && !empty($citeText) ) :
						?>
						<div id="<?php echo "btn_insert_original_text_{$this->request->data['Entry']['id']}"; ?>">
							<?php
							echo $this->Html->scriptBlock("var quote_{$this->request->data['Entry']['id']} = " . json_encode($citeText) . "; ",
									array( 'inline' => 'true' ));
							// empty the textarea
							echo $this->Html->scriptBlock("$('#markItUp_{$this->request->data['Entry']['id']} #EntryText').val('')",
									array( 'inline' => 'true' ));
							echo $this->Html->link(
									Configure::read('Saito.Settings.quote_symbol') . ' ' . __('Cite'),
									'#',
									array(
									'onclick' => "$('#markItUp_{$this->request->data['Entry']['id']} #EntryText').val(quote_{$this->request->data['Entry']['id']} + '" . '\n\n' . "' + $('#markItUp_{$this->request->data['Entry']['id']} #EntryText').val());"
									. "$('#btn_insert_original_text_{$this->request->data['Entry']['id']}').slideToggle();"
									. "$('#markItUp_{$this->request->data['Entry']['id']} #EntryText').focus();"
									. "return false;",
									'class' => 'label',
									)
							);
							?>
						</div>
						<br/>
						<?php
					endif; //*** add original posting contents
					?>

					<div class="checkbox">
						<?php
						/*
						 * nsfw checkbox start
						 */

						echo $this->Form->checkbox('nsfw');
						echo $this->Form->label('nsfw', __('entry_nsfw_title'));

						/*
						 * nsfw checkbox end
						 */
						?>
					</div>
					<div class="checkbox">
						<?
						// ### flattr checkbox start
						if ( Configure::read('Saito.Settings.flattr_enabled') == TRUE && $CurrentUser['flattr_uid'] == TRUE ) :
							echo $this->Form->checkbox('flattr');
							echo $this->Form->label('flattr', __('entry_flattr_this_posting'));

							// ### JS code for dynamicaly switching the checkbox accordingly to category
							$code_insert = "
									var elements = [" . implode(",",
											$category_flattr) . "];
									if ( elements.indexOf(parseInt(data)) >= 0 ) {
											$('#EntryFlattr').attr('checked', true);
										} else {
											$('#EntryFlattr').attr('checked', false);
										}";

							if ( $CurrentUser['flattr_allow_posting'] == FALSE ) {
								$code_insert .= "$('#EntryFlattr').attr('checked', false);";
							}

							if ( $this->getVar('isAjax') ) {
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
				</div> <!-- postingform_right -->
				<div class="postingform_main">
					<?php
					# @bogus
					if ( !$this->getVar('isAjax') || (isset($referer_action) && ( $referer_action == 'mix' || $referer_action == 'view' || $referer_action == 'add' ) ) ) {
						echo $this->Form->submit(__('submit_button'),
								array(
								'class' => 'btn_submit',
								'tabindex' => 4,
								'onclick' => "this.disabled=true; this.form.submit();",
						));
					} # !isAjax()
					else {
						$js_r = "new ThreadLine('{$this->request->data['Entry']['id']}').insertNewLineAfter(data);";
						if ( $CurrentUser['inline_view_on_click'] ) {
							$js_r .= "$('.link_show_thread').bind('click', function () { new ThreadLine($(this)[0].id.slice($(this)[0].id.lastIndexOf('_') + 1)).load_inline_view(); return false;} ); ";
						}
						$js_r .= "$('.btn_submit').removeAttr('disabled');";
						echo $this->Ajax->submit(
								__('submit_button'),
								array(
								'url' => array(
										'controller' => 'entries',
										'action' => 'add',
										$this->request->data['Entry']['id'],
								),
								'beforeSubmit' => "$('.btn_submit').attr('disabled', 'disabled');",
								'class' => 'btn_submit',
								'tabindex' => 4,
								'inline' => true,
								'success' => $js_r,
								)
						);
					}
					?>

					<?
					$js_r = $this->Js->get('#preview_' . $this->request->data['Entry']['id'])->effect('slideIn',
									array( 'speed' => 'fast' ));
					$js_r .= "$('#preview_slider_" . $this->request->data['Entry']['id'] . "').html('');";
					echo $this->Ajax->submit(
							__('preview'),
							array(
							'url' => array(
									'controller' => 'entries',
									'action' => 'preview',
							),
							'loading' => $js_r,
							'id' => 'btn_preview_' . $this->request->data['Entry']['id'],
							'update' => 'preview_slider_' . $this->request->data['Entry']['id'],
							'class' => 'btn_preview',
							'indicator' => 'spinner_preview_' . $this->request->data['Entry']['id'],
							'tabindex' => 5,
							'inline' => true,
							'position' => 'after',
							)
					);
					?>
				</div> <!-- postingform_main -->
			</div> <!-- container -->
<?= $this->Form->end(); ?>
		</div> <!-- content -->
	</div> <!-- postingform -->
</div> <!-- entry add/reply -->

<?php echo ($this->getVar('isAjax')) ? $this->Js->writeBuffer() : ''; ?>