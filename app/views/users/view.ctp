<div id="user_view" class="user view">
	<?php 
		$linkToHistory = $html->link(
												__('user_show_entries', true),
												array(
														'controller' 	=> 'entries',
														'action'			=> 'search',	
														'name'				=> $user['User']['username'],
														'month'				=> strftime('%m', strtotime($user['User']['registered'])),
														'year'				=> strftime('%Y', strtotime($user['User']['registered'])),
														'adv'					=> 1,
														) ,
												array('escape' => false)
												);
		$table =
			array (
					array (
						__('username_marking', true),
						$user['User']['username'] . " <span class='info_text'>({$userH->type($user['User']['user_type'])})</span>", # @td user_type for mod and admin
					),
				);
		
		if (!empty($user['User']['user_real_name'])) {
			$table[] = 	array (
							__('user_real_name', true),
							$userH->minusIfEmpty($user['User']['user_real_name']),
						);
			}
		if (!empty($user['User']['user_email']) && $user['User']['personal_messages'] == TRUE) {
			$table[] = 	
					array (
						__("userlist_email", true),
						$userH->minusIfEmpty($userH->contact($user['User'])),
					);
			}
		if (!empty($user['User']['user_hp'])) {
			$table[] = 	
					array (
						__("user_hp", true),
						$userH->minusIfEmpty($userH->homepage($user['User']['user_hp'])),
					);
			}
		if (!empty($user['User']['user_place'])) {
			$table[] = 	
					array (
							__('user_place', true),
							$user['User']['user_place'],
					);
			}
		$table = array_merge($table,
			array(
					array (
							__('user_since', true),
							strftime(__('date_short', true), strtotime($user['User']['registered'])),
					),
					array (
							__('user_postings', true),
								$user['User']['number_of_entries'] .
								' ('.  $userH->userRank($user["User"]['number_of_entries']) . ') [' . $linkToHistory . ']',
					),
			));

		if (!empty($user['User']['profile'])) {
			$table[] = 	
					array (
							__('user_profile', true),
							$bbcode->parse($user['User']['profile']),
					);
			}

		if (!empty($user['User']['signature'])) {
			$table[] = 	
					array (
							__('user_signature', true),
							$bbcode->parse($user['User']['signature']),
					);
			}

			//* flattr Button
			if($user['User']['flattr_allow_user'] == TRUE && Configure::read('Saito.Settings.flattr_enabled') == TRUE) {
				$table[] =	array (
							__('flattr', true),
							$flattr->button('', 
									array( 
										'uid' => $user['User']['flattr_uid'],
										'language'	=> Configure::read('Saito.Settings.flattr_language'),
										'title' => '['.$_SERVER['HTTP_HOST'].'] '.$user['User']['username'] ,
										'description' => '['.$_SERVER['HTTP_HOST'].'] '.$user['User']['username'],
										'cat' => Configure::read('Saito.Settings.flattr_category'),
										'button' => 'compact',
									)
								),
					);
			}

	?>

	<div class="box_style_1">
		<div class="c_header_2">
			<div>
				<div class='c_first_child'></div>
				<div><h1><?php echo $textH->properize( $user['User']['username'] ) . ' ' . __('user_profile', true);?></h1> </div>
				<div class='c_last_child'></div>
			</div>
		</div>	
		<div class="content">
			<table class='c_table_clean_1 c_table_header_left'>
			<?php echo 		$html->tableCells($table); ?> 
			</table>
		</div>
		<? if (  $allowedToEditUserData ) : ?>
		<div  class="c_a_a_b">
			<div>
				<div class="c_a_a_b_a c_first_child">
						<?= $html->link(
													__('edit_userdata', true),
													array( 'action' => 'edit', $user['User']['id'] ),
													array( 'id'	=> 'btn_user_edit', 'class' => 'btn_submit' )
										); ?>
				</div> <!-- c_a_a_b_a c_first_child -->
				<div class="c_a_a_b_b"> 
				</div><!-- c_a_a_b_b -->
<!--				<div class="c_a_a_b_c c_last_child">-->
<!--				</div>  c_a_a_b_c c_last_child -->
			</div>
		</div><!-- c_a_a_b -->
		<? endif; ?>
	</div>
	<br/>
	<br/>

	<div class="box_style_1">
		<div class="c_header_2">
			<div>
				<div class='c_first_child'></div>
				<div><h1><?php echo $textH->properize( $user['User']['username'] ) . ' ' . __('user_recentposts', true); // @lo  ?>
						
					</h1> </div>
				<div class='c_last_child'></div>
			</div>
		</div>	
		<div class="content">
			<? if (isset($lastEntries) && !empty($lastEntries)) : ?>
			<ul>
				<? foreach ($lastEntries as $entry) : ?>
				<li>
					<?= $this->element('entry/thread_cached', array ( 'entry_sub' => $entry, 'level' => 0 )); ?>
				</li>
				<? endforeach; ?>
			</ul>
		<? endif ; ?>
		</div>
	</div>


</div>