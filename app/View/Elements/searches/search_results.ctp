<div class="search_results panel">
	<div class="panel-content">
		<?php
			if (empty($results)) {
				echo $this->element('generic/no-content-yet',
						[
								'message' => __('search_nothing_found')
						]);
			} else {
				$out = [];
				foreach ($results as $result) {
					$out[] = $this->EntryH->threadCached($result, $CurrentUser);
				}
				echo $this->Html->nestedList($out);
			}
		?>
	</div>
</div>
