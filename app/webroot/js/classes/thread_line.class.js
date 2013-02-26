/**
 * Class ThreadLine
 *
 * A single thread/line in a thread tree
 */
function ThreadLine(id) {
  this.id = id;
  this.id_thread_slider		=	'#t_s_' + id;
};

/**
 * loads a posting inline via ajax and shows it
 */
ThreadLine.prototype.load_inline_view = function (options, scroll) {
	if (typeof scroll == 'undefined' ) scroll = true;
	var id = this.id;
	var p = this;

	jQuery.ajax(
	{
		beforeSend:function(request) {
			request.setRequestHeader('X-Update', 't_s_' + id );
		},
		complete:function(request, textStatus) {
		// show inline posting
		// @td the scroll from p.showInlineView(scroll);
		},
		success:function(data, textStatus) {
			jQuery( p.id_thread_slider ).html(data);
			postings.add([{
				id: id
			}]);
			new PostingView({
				el: $('.js-entry-view-core[data-id=' + id + ']'),
				model: postings.get(id),
                eventBus: window.eventBus,
                webroot: SaitoApp.app.settings.webroot
			});

			if (typeof options !== 'undefined' && typeof options.success !== 'undefined') {
				options.success();
			}

		/*
				var here = document.URL;
				history.replaceState(null, '', $(p.id_thread_line).find('a.thread_line-content').attr('href'));
				history.replaceState(null, '', here);
				*/
		},
		async:true,
		type:'post',
		url: SaitoApp.app.settings.webroot + 'entries/view/'  + id
	}
	);
};

/**
 * Adds an new thread as answer after the current and fills it with `data`
 */
ThreadLine.prototype.insertNewLineAfter = function (data) {
	var tid = $(data).find('.js-thread_line').data('tid');
	threads.get(tid).threadlines.get(this.id).set({isInlineOpened: false});
	postings.get(this.id).set({isAnsweringFormShown: false});
  var el = $('<li>'+data+'</li>').insertAfter('#ul_thread_' + this.id + ' > li:last-child');

	// add to backbone model
	var threadLineId = $(data).find('.js-thread_line').data('id');
	threads.get(tid).threadlines.add([{
		id: threadLineId,
		isNewToUser: true,
		isAlwaysShownInline: SaitoApp.currentUser.user_show_inline
	}], {silent: true});
	new ThreadLineView({
		el: $(el).find('.js-thread_line'),
		model: threads.get(tid).threadlines.get(threadLineId)
	});
};