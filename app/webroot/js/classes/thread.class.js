/**
 * Class Thread
 *
 * A complete thread tree
 */
function Thread(tid) {
	this.tid = tid;

	this.btns_show_new 	= '.btn_show_thread_tid.' + this.tid + '.new';
	this.btns_show 			= '.btn_show_thread_tid.' + this.tid ;
	this.toolbox 				= '.thread_tools.' + this.tid ;
	this.threadbox 			= '.thread_box.' + this.tid ;
};

Thread.init = function() {
	// highlight for Toolbar
	Thread.initHighlightTools();
};

Thread.initHighlightTools =  function () {
		$('.thread_box').each(
			function() {
				var elem = $(this);
				elem.hoverIntent(
					function () {
						$('.thread_tools', elem).delay(50).fadeTo(200, 1) ;
					},
					function () {
						$('.thread_tools', elem).delay(400).fadeTo(1000, 0.2);
					}
				);
			}
		);
};

Thread.scrollTo = function(tid) {
	var toolbox	= '.thread_box.' + tid ;
	scrollToTop($(toolbox));
};
