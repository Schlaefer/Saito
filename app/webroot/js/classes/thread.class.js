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

Thread.scrollTo = function(tid) {
	var toolbox	= '.thread_box.' + tid ;
	scrollToTop($(toolbox));
};