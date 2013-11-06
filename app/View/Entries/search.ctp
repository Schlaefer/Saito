<div class="entry search">
	<div class="search_form_wrapper" style="<?php
if ( isset($this->request->params['data']['Entry']['adv']) ) {
	echo "display:none;";
}
?>">
		<div style="width: 20%;">
		</div>
		<div>
			<?php
			echo $this->Form->create(
				null,
				array(
					'url' => array_merge(
						array('action' => 'search'),
						$this->request->params['pass']
					),
					'type' => 'get',
					'class' => 'search_form shp shp-bottom',
					'style' => 'height: 40px;',
					'inputDefaults' => array('div' => false, 'label' => false),
					'data-title'    => __('Help'),
					'data-content'  => __('search_fulltext_textfield_shp')
				)
			);
			echo $this->Form->submit(__('search_submit'),
					array( 'div' => false, 'class' => 'btn btn-submit btn_search_submit' ));
			?>
			<div>
				<?php
				echo $this->Form->input('search_term',
						array(
						'div' => false,
            'id'    => 'search_fulltext_textfield',
						'class' => 'search_textfield',
            'style' => 'height: 38px;',
						'placeholder' => __('search_term'),
						'value' => $search_term,
						)
				);
			?>
			</div>
			<?php
			echo $this->Form->end();
			?>
		</div>
		<div style="width: 20%;">
			<a href="#" onclick="$('.search_form_wrapper').slideToggle('', function (){$('.search_form_wrapper_adv').slideToggle();});return false;">
<?php echo __('search_advanced'); ?>
			</a>
		</div>
	</div> <!-- search_form_wrapper -->
	<div class="search_form_wrapper_adv box-form" style="<?php
if ( !isset($this->request->params['data']['Entry']['adv']) ) {
	echo "display:none;";
}
?>">
    <div class="content">
				 <?php
				 echo $this->Form->create('Entry',
						 array(
						 'url' => array_merge(array( 'action' => 'search' ), $this->request->params['pass']),
				 ));
				 ?>
		<div>
			<?php
				echo $this->Form->input('subject',
						array( 'div' => false, 'label' => __('subject'), 'required' => false )
				);
			?>
		</div>
		<div><?php echo $this->Form->input('text',
					array( 'div' => false, 'label' => __('Text'), 'type' => 'text' )); ?> </div>
		<div><?php echo $this->Form->input('name',
					array( 'div' => false, 'label' => __('user_name') )); ?> </div>
		<div>
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
			<?php echo __("search_since"); ?>:
			<?php
				echo $this->Form->month(
						'Entry'
						, array('value' => $this->request->data['Entry']['month'] )
					);
				?>
			<?php
				echo $this->Form->year(
						'Entry',
						$start_year,
						date('Y'),
						array('value' => $this->request->data['Entry']['year'])
				); ?>
		</div>
		<div><?php echo $this->Form->input('adv',
					array( 'type' => 'hidden', 'value' => 1 )); ?> </div>
		<div>
<?php echo $this->Form->submit(__('search_submit'), array( 'class' => 'btn btn-submit' )); ?>
			<a href="#" onclick="$('.search_form_wrapper_adv').slideToggle('', function (){$('.search_form_wrapper').slideToggle();}); return false;">
				&nbsp;<?php echo __('search_simple'); ?>
			</a>
		</div>
<?php echo $this->Form->end(); ?>
    </div> <!-- content -->
	</div> <!-- search_form_wrapper_adv -->
	<div class="search_results box-content">
<?php if ( isset($this->Paginator) && !empty($FoundEntries) ) : ?>
			<div class="l-box-header box-header">
				<div>
					<div class="c_first_child"></div>
					<div></div>
					<div class="c_last_child">
						<?php
						/**
						 * Paremters passed by paginator links
						 */
						if ( isset($this->passedArgs['search_term']) ):
							$this->Paginator->options(array( 'url' => array( 'search_term' => $this->passedArgs['search_term'] ) ));
						else:
							unset($this->passedArgs['search_term']);
							$this->Paginator->options(array( 'url' => array_merge( array( ), $this->passedArgs) ));
						endif;
						?>
						<?php
						if ( $this->Paginator->hasPrev() )
							echo $this->Paginator->prev(
                  '<i class="fa fa-chevron-left"></i>',
									array( 'escape' => false ), null, array( 'class' => 'disabled' ));
						?>
						<?php
						echo $this->Paginator->counter(array( 'format' => '%page%/%pages%' ));
						?>
						<?php
						if ( $this->Paginator->hasNext() )
							echo $this->Paginator->next(
                  '<i class="fa fa-chevron-right"></i>',
									array( 'escape' => false ), null, array( 'class' => 'disabled' ));
						?>
					</div>
	<?php
	?>
					&nbsp;
				</div>
			</div> <!-- header -->
				<?php  endif; ?>
		<div class="content">
					<?php  if ( isset($FoundEntries) && !empty($FoundEntries) ) : ?>
				<ul>
						<?php  foreach ( $FoundEntries as $entry ) : ?>
						<li>
						<?php echo $this->EntryH->threadCached($entry, $CurrentUser); ?>
						</li>
				<?php  endforeach; ?>
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
		</div> <!-- content -->
	</div> <!-- search_results -->
</div> <!-- entry_search -->