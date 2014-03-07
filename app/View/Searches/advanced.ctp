<?php
	SDV($results, []);

	$this->element('searches/search_navigation', ['results' => $results]);
?>

<div class="search advanced">
	<div class="panel">
		<div class="panel-content panel-form">
			<?php
				echo $this->Form->create('Entry',
						[
								'url' => array_merge(array('controller' => 'searches', 'action' => 'advanced'),
										$this->request->params['pass']),
						]);
				echo $this->Form->input('subject',
						['label' => __('subject'), 'required' => false]);
				echo $this->Form->input('text',
						['label' => __('Text'), 'type' => 'text']);
				echo $this->Form->input('name',
						[
								'label' => __('user_name'),
								'div' => ['style' => 'width: 63%; display: inline-block;']
						]);
				echo $this->Form->input('nstrict',
						[
								'type' => 'checkbox',
								'checked' => !empty($this->request->query['nstrict']),
								'div' => ['style' => 'display: inline-block; padding: 1em;'],
								'label'	 => __('search.nstrict'),
								'value' => 1
						]);
			?>
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
					);
					echo '&nbsp;&nbsp;' . __("search_since") . ':';
					echo $this->Form->month(
							'Entry',
							['value' => $month]
					);
					echo $this->Form->year('Entry',
							$start_year,
							date('Y'),
							['value' => $year]);
				?>
			</div>
			<div class="input">
				<?=
					$this->Form->submit(__('search_submit'),
							['class' => 'btn btn-submit']) ?>
			</div>
			&nbsp;

			<?=
				$this->Html->link(__('search_simple'),
						[
								'controller' => 'searches',
								'action' => 'simple'
						]) ?>
			<?php echo $this->Form->end(); ?>
		</div>
	</div>

	<?= $this->element('searches/search_results', ['results' => $results]) ?>
</div>