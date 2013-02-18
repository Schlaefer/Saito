<?php
  $this->start('headerSubnavLeft');
  echo $this->Html->link(
      '<i class="icon-arrow-left"></i>&nbsp; ' . $headerSubnavLeftTitle ,
      $headerSubnavLeftUrl,
      array( 'class' => 'textlink', 'escape' => FALSE ));
  $this->end();
?>
<?php
// new entries have no id (i.e. no reply an no edit), so wie set a filler var
if ( !isset($this->request->data['Entry']['id']) ) {
	$this->request->data['Entry']['id'] = 'foo';
}

// cite entry text if necessary
if ( $this->getVar('citeText') ) {
	$citeText =  $this->Bbcode->citeText($this->getVar('citeText'));
}

?>
<div id ="entry_<?php echo  ($this->request->is('ajax')) ? 'reply' : 'add'; ?>" class="entry <?php echo  ($this->request->is('ajax')) ? 'reply' : 'add'; ?>">

	<div class="preview">
		<div class="l-box-header box-header">
			<div>
        <div class="c_first_child">
					<i class='icon-close-widget icon-large pointer btn-icon-close btn-previewClose'>&nbsp;</i>
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
		</div><!-- header -->

		<div class="content"></div>
	</div> <!-- preview -->

	<div class="postingform">
		<div class="l-box-header box-header">
			<div>
        <div class="c_first_child">
<?php  if ( $this->request->is('ajax') ) : ?>
						<i class='icon-close-widget icon-large btn-icon-close pointer btn-answeringClose'>
								&nbsp;
            </i>
<?php  endif; ?>
				</div>
				<div>
					<h2>
<?php echo  $form_title; ?>
					</h2>
				</div>
				<div class="c_last_child">&nbsp;</div>
			</div>
		</div>

		<div id="markitup_upload">
            <div class="body">
            </div>
		</div>

		<div id='markitup_media' style="display: none; overflow: hidden;">
			<?php echo 
			$this->Form->create(FALSE,
					array(
					'url' => '#',
					'style' => 'width: 100%;' ));
			?>
			<?php echo 
			$this->Form->label(
					'media', 'Bitte Verweis oder Code zum Einbinden angeben:',
					array(
					'class' => 'c_markitup_label',
					)
			);
			?>
			<?php echo 
			$this->Form->textarea('media',
					array(
					'id' => 'markitup_media_txta',
					'class' => 'c_markitup_popup_txta',
					'rows' => '6',
					'columns' => '20',
			));
			?>
			<div class="clearfix"></div>
<?php echo 
$this->Form->submit(__('Einfügen'),
		array( // @lo
		'style' => 'float: right;',
		'class' => 'btn btn-submit',
		'id' => 'markitup_media_btn',
));
?>
					<?php echo  $this->Form->end(); ?>
			<div class="clearfix"></div>
			<br/>
			<div id="markitup_media_message" class="flash error" style="display: none;">
					<?php echo __('No video recognized.'); ?>
			</div>
		</div>

		<div class="content">
					<?php echo  $this->Form->create('Entry'); ?>
			<div class="bp_container">
					<?php echo $this->EntryH->getCategorySelectForEntry($categories,
							$this->request->data); ?>
				<div class="postingform_main">
					<?php echo 
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
						echo $this->MarkitupEditor->getButtonSet('markItUp_' . $this->request->data['Entry']['id']);
						echo $this->MarkitupEditor->editor(
								'text',
								array( 
										'parser' => false,
                    'set' => 'default', 'skin' => 'macnemo',
                    'label' => false, 'tabindex' => 3,
                    'settings' => 'markitupSettings' )
                );
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
						echo $this->Form->checkbox('Event.1.event_type_id', array(
								'checked' => isset($notis[0]) && $notis[0],
						));
						echo $this->Form->label('Event.1.event_type_id', __('Notify on reply'));
						?>
					</div>
					<div class="checkbox">
					<?php
						echo $this->Form->checkbox('Event.2.event_type_id', array(
								'checked' => isset($notis[1]) && $notis[1],
						));
						echo $this->Form->label('Event.2.event_type_id', __('Notify on thread replies'));
						?>
					</div>
					<hr/>
					<div class="checkbox">
						<?php
						echo $this->Form->checkbox('nsfw');
						echo $this->Form->label('nsfw', __('entry_nsfw_title'));
						?>
					</div>
					<div class="checkbox">
						<?php
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

							if ( $this->request->is('ajax') ) {
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
					if ( !$this->request->is('ajax') || (isset($lastAction) && ( $lastAction === 'mix' || $lastAction === 'view' || $lastAction === 'add' ) ) ) {
						echo $this->Form->submit(__('submit_button'),
								array(
								'id' => 'btn-submit',
								'class' => 'btn btn-submit',
								'tabindex' => 4,
								'onclick' => "this.disabled=true; this.form.submit();",
						));
					} # !i$this->request->is('ajax')
					else {
						$js_r = "new ThreadLine('{$this->request->data['Entry']['id']}').insertNewLineAfter(data);";
						$js_r .= "$('.btn.btn-submit').removeAttr('disabled');";
						echo $this->Js->submit(
								__('submit_button'),
								array(
								'url' => array(
										'controller' => 'entries',
										'action' => 'add',
										$this->request->data['Entry']['id'],
								),
								'id' => 'btn-submit',
								'beforeSend' => "$('.btn.btn-submit').attr('disabled', 'disabled');",
								'class' => 'btn btn-submit',
								'tabindex' => 4,
								'buffer' => false,
								'success' => $js_r,
								)
						);
					}
					?>
					&nbsp;
					<?php
					echo $this->Html->link(
							__('preview'),
							'#',
							array(
								'class' => 'btn btn-preview',
								'tabindex' => 5
							)
					);
					?>
				</div> <!-- postingform_main -->
			</div> <!-- container -->
<?php echo  $this->Form->end(); ?>
		</div> <!-- content -->
	</div> <!-- postingform -->
</div> <!-- entry add/reply -->
<div class="posting_formular_slider_bottom"></div>

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