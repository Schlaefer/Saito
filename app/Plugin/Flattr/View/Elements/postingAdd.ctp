<?php if ($CurrentUser['flattr_uid'] == true) : ?>
	<div class="checkbox">
		<?php
			echo $this->Form->checkbox('flattr');
			echo $this->Form->label(
				'flattr',
				__d('flattr', 'flattr_this_posting')
			);

			//= JS code for dynamicaly switching the checkbox accordingly to category
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
				// if it's an answer
				$code = "$(document).ready(function (){
										var data = " . $this->request->data['Entry']['category'] . ";
										$code_insert
									});";
			} else {
				// if it's a new posting
				$code = "$(document).ready(function () { $('#EntryCategory').change(function() {
										var data = $(this).val();
										$code_insert
									})});";
			}
			echo $this->Html->scriptBlock($code);
		?>
	</div>
<?php endif; ?>
