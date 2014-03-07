<?php
	$this->start('headerSubnavRightTop');
	if (!empty($results)) {
		if ($this->Paginator->current() > 2) {
			echo $this->Paginator->first(
					'<i class="fa fa-chevron-left"></i><i class="fa fa-chevron-left"></i>',
					['escape' => false, 'style' => 'padding-right: 1em'],
					null,
					['class' => 'disabled']);
		}

		if ($this->Paginator->hasPrev()) {
			echo $this->Paginator->prev(
					'<i class="fa fa-chevron-left"></i>',
					['escape' => false],
					null,
					['class' => 'disabled']);
			echo '&nbsp;';
		}

		echo $this->Paginator->counter(['format' => '%page%/%pages%']);

		if ($this->Paginator->hasNext()) {
			echo '&nbsp;';
			echo $this->Paginator->next(
					'<i class="fa fa-chevron-right"></i>',
					['escape' => false],
					null,
					['class' => 'disabled']);
		}
	}
	$this->end();
