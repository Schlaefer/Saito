<? if ( $CurrentUser->isLoggedIn()  && $this->request->params['action'] == 'index' && $this->request->params['controller'] == 'entries') : ?>
	<?php echo $this->element('layout/slidetabs__header', array('id' => 'recentposts', 'btn_class' => 'img_userhistory')); ?>
				<div class="slidetab_rp">
					<ul class="slidetab_tree">
						<li>
							<span title='The sea was angry that day my friends, like an old man trying to return soup at a deli …'>
								<?php 
									// @lo
									echo $this->TextH->properize( $CurrentUser['username'] ). ' ' . __('user_recentposts');
								?> 
							</span>
						</li>
		<? if (isset($lastEntries) && !empty($lastEntries)) : ?>
						<li>
						<ul class="c_slidetab_subtree">
							<? foreach ($lastEntries as $entry) : ?>
							<li>
								<span class="img_thread"></span>
								<?php
//									if ( strlen($entry['Entry']['subject']) > 20 ) {
//										$s = html_entity_decode($entry['Entry']['subject'], ENT_QUOTES);
//										$sub = mb_substr($s, 0, 20);
//										$entry['Entry']['subject'] = htmlentities($sub);// . '…';
//										}
									$entry['Entry']['subject'] = '' . $entry['Entry']['subject'];										
								?>
								<?php echo $this->EntryH->getFastLink($entry); ?><br/> <span class='c_info_text'><?php echo $this->TimeH->formatTime($entry['Entry']['time']); ?></span>
							</li>
							<? endforeach; ?>
						</ul>
		<? endif ; ?>
					</ul>
				</div>
	<?php echo $this->element('layout/slidetabs__footer'); ?>
<? endif; ?>