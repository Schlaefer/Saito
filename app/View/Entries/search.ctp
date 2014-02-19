<?php
	// Navigation header subnav right
	// -------------------------------------
	$this->start('headerSubnavRightTop');
	if (isset($this->Paginator) && !empty($FoundEntries)) {
		// Paremters passed by paginator links
		if (isset($this->passedArgs['search_term'])) {
			$_options = ['url' => ['search_term' => $this->passedArgs['search_term']]];
		} else {
			unset($this->passedArgs['search_term']);
			$_options = ['url' => array_merge([], $this->passedArgs)];
		}
		$this->Paginator->options($_options);
		if ($this->Paginator->hasPrev()) {
			echo $this->Paginator->prev(
					'<i class="fa fa-chevron-left"></i>',
					['escape' => false],
					null,
					['class' => 'disabled']);
			echo '&nbsp;';
		}
		echo $this->Paginator->counter(array('format' => '%page%/%pages%'));
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

	// setup
	// -------------------------------------
	$_isAdvancedSearch = isset($this->request->params['data']['Entry']['adv']);
?>
<div class="entry search <?= ($_isAdvancedSearch) ? 'is-advanced' : '' ?>">
	<div class="search_form_wrapper">
		<div style="width: 20%;"></div>
		<div>
			<?php
				echo $this->Form->create(
						null,
						[
								'url' => array_merge(['action' => 'search'],
										$this->request->params['pass']),
								'type' => 'get',
								'class' => 'search_form',
								'style' => 'height: 40px;',
								'inputDefaults' => ['div' => false, 'label' => false]
						]
				);
				echo $this->Form->submit(__('search_submit'),
						[
								'div' => false,
								'class' => 'btn btn-submit btn_search_submit'
						]);
			?>
			<div>
				<?=
					$this->Form->input('search_term',
							[
									'div' => false,
									'id' => 'search_fulltext_textfield',
									'class' => 'search_textfield shp',
									'data-shpid' => 1,
									'style' => 'height: 38px;',
									'placeholder' => __('search_term'),
									'value' => $search_term
							]
					);
				?>
			</div>
			<?= $this->Form->end(); ?>
		</div>
		<div style="width: 20%;">
			<a href="#" onclick="$('.search_form_wrapper').slideToggle('', function (){$('.search_form_wrapper_adv').slideToggle();});return false;">
				<?= __('search_advanced') ?>
			</a>
		</div>
	</div> <!-- search_form_wrapper -->
	<div class="search_form_wrapper_adv panel">
		<div class="panel-content panel-form">
			<?=
				$this->Form->create('Entry',
						[
								'url' => array_merge(array('action' => 'search'),
										$this->request->params['pass']),
						]);
			?>
			<div class="input">
				<?=
					$this->Form->input('subject',
							['div' => false, 'label' => __('subject'), 'required' => false]
					)
				?>
			</div>
			<div class="input">
				<?=
					$this->Form->input('text',
							[
									'div' => false,
									'label' => __('Text'),
									'type' => 'text'
							]) ?>
			</div>
			<div class="input">
				<?=
					$this->Form->input('name',
							['div' => false, 'label' => __('user_name')]) ?>
			</div>
			<div class="input">
				<?=
					$this->Form->select(
							'category',
							$categories,
							[
									'value' => $this->request->data['Entry']['category'],
									'empty' => __('All Categories'),
									'required' => false
							]
					)
				?>
				&nbsp;
				<?= __("search_since") ?>:
				<?php
					echo $this->Form->month(
							'Entry'
							,
							array('value' => $this->request->data['Entry']['month'])
					);
					echo $this->Form->year(
							'Entry',
							$start_year,
							date('Y'),
							array('value' => $this->request->data['Entry']['year'])
					); ?>
			</div>
			<div>
				<?=
					$this->Form->input('adv',
							['type' => 'hidden', 'value' => 1]); ?>
			</div>
			<div class="input">
				<?=
					$this->Form->submit(__('search_submit'),
							['class' => 'btn btn-submit']) ?>
			</div>
			&nbsp;
			<a href="#"
				 onclick="$('.search_form_wrapper_adv').slideToggle('', function (){$('.search_form_wrapper').slideToggle();}); return false;">
				<?= __('search_simple') ?>
			</a>
			<?php echo $this->Form->end(); ?>
		</div>
	</div>

	<div class="search_results panel">
		<div class="panel-content">
			<?php if (isset($FoundEntries) && !empty($FoundEntries)) : ?>
				<ul>
					<?php foreach ($FoundEntries as $entry) : ?>
						<li>
							<?php echo $this->EntryH->threadCached($entry, $CurrentUser); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<?php
				echo $this->element(
						'generic/no-content-yet',
						array(
								'message' => __('search_nothing_found')
						)
				);
				?>
			<?php endif; ?>
		</div>
	</div>
</div>