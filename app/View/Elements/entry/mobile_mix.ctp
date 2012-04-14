<?php
/**
 * setup
 */
$level = 0;

?>
<ul class="posting">
	<li>
		<a name="<?php echo $entry_sub['Entry']['id']; ?>"></a>
		<div data-role="collapsible" <?php
			if ( !$this->EntryH->isNewEntry($entry_sub, $CurrentUser) OR $this->EntryH->isNt($entry_sub) ) echo 'data-collapsed="true"';
			if ( $this->EntryH->isNewEntry($entry_sub, $CurrentUser) ) echo 'data-theme="b"';
			?>>
			<h2 class-="ui-li-heading">
				<?php
				echo $this->EntryH->getSubject($entry_sub);
				?>
				<span style="font-weight: normal; white-space: nowrap;">&nbsp;–&nbsp;<?php echo $entry_sub['User']['username'] ?></span>
			</h2>
			<p class="ui-li-desc">
				<?php
				echo $this->TimeH->formatTime($entry_sub['Entry']['time']);
				?>
			</p>
			<br />
			<div>
		<?php echo $this->Bbcode->parse($entry_sub['Entry']['text']); ?>
			</div>
		</div>
		<?php  if ( isset($entry_sub['_children']) ) : ?>
			<?
			foreach ( $entry_sub['_children'] as $child ) :
				echo $this->element('entry/mobile_mix',
						array( 'entry_sub' => $child, $level = $level + 1 ));
			endforeach;
			?>
<?php  endif ?>

	</li>
</ul>