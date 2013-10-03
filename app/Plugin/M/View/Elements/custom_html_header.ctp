<script>
	window.Saito.callbacks = {
		afterEntriesIndex: function() {
			var add = $('#macfix-ad-1').html();
			this.$('li:first').after('<li class="macfix-ad">' + add + '</li>');
		},
		afterEntriesMix: function() {
			var add = $('#macfix-ad-1').html();
			this.$('.panel div:first').after(add);
		},
		afterAppmenu: function() {
			var add = $('#afterAppmenu').html();
			this.$('ul').append(add);
		}
	}
</script>

<script id="macfix-ad-1" type="text/x-handlebars-template">
	<!-- START MacFix Ad Code [mobile] -->
	<p align="center">
		<script type="text/javascript" src="http://www.macfix.de/adpeeps/adpeeps.php?bf=showad&amp;uid=100000&amp;bmode=off&amp;gpos=center&amp;bzone=mf_mobile&amp;bsize=custom&amp;btype=3&amp;bpos=default&amp;btotal=1&amp;btarget=_blank&amp;bborder=0">
		</script>
	</p>
		<!-- END MacFix Ad Code -->
</script>

<script id="afterAppmenu" type="text/x-handlebars-template">
	<li>
		<a href='http://www.macfix.de/'>macfix.de</a>
	</li>
</script>
