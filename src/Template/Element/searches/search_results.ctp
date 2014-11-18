<div class="search_results panel">
	<div class="panel-content">
		<?php
			if (empty($results)) {
				echo $this->element('generic/no-content-yet',
						[
								'message' => __('search_nothing_found')
						]);
			} else {
				foreach ($results as $result) {
					echo $this->EntryH->renderThread($result, ['rootWrap' => true]);
				}
			}
		?>
	</div>
</div>
