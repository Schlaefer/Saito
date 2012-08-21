// til all is moved to bb global objects are needed in the classes
//	(function ($) {

var ThreadLineModel = Backbone.Model.extend({
	defaults: {
		isContentLoaded: false,
		isInlineOpened: false,
		isAlwaysShownInline: false
	},
	loadContent: function() {
		new ThreadLine(this.get('id')).load_inline_view();
	}
});

var ThreadLineCollection = Backbone.Collection.extend({
	model: ThreadLine
});

var ThreadLineView = Backbone.View.extend({

	className: 'thread_line',

	events: {
		'click .btn_show_thread': 'toggleInlineOpen',
		'click .link_show_thread': 'toggleInlineOpen'
	},

	toggleInlineOpenFromLink: function(event) {
		if (this.model.get('isAlwaysShownInline')) {
			this.toggleInlineOpen(event);
		}
	},

	toggleInlineOpen: function(event) {
		event.preventDefault();
		if (!this.model.get('isInlineOpened')) {
			if (!this.model.get('isContentLoaded')) {
				this.model.loadContent();
			}
			this.model.set({
				isInlineOpened: true
			});
		} else {
			this.model.set({
				isInlineOpened: false
			});
		}
	}
});

var ThreadModel = Backbone.Model.extend({

	defaults: {
		isThreadCollapsed: false
	},

	toggleCollapseThread: function() {
		this.set({
			isThreadCollapsed: !this.get('isThreadCollapsed')
		});
	}

});

var ThreadCollection = Backbone.Collection.extend({
	model: ThreadModel,
	localStorage: new Store('Threads')
})

var ThreadView = Backbone.View.extend({

	className: 'thread_box',

	events: {
		"click .btn-threadCollapse":  "collapseThread"
	},

	initialize: function(){
		this.model.on('change:isThreadCollapsed', this.toggleCollapseThread, this);

		if (this.model.get('isThreadCollapsed')) {
			this.hide();
		}
	},

	collapseThread: function(event) {
		event.preventDefault();
		this.model.toggleCollapseThread();
		this.model.save();
	},

	toggleCollapseThread: function(model, isThreadCollapsed) {
		if(isThreadCollapsed) {
			this.slideUp();
		} else {
			this.slideDown();
		}
	},

	slideUp: function() {
		$(this.el).find('.tree_thread > ul > li:not(:first-child)').slideUp('100');
		$(this.el).find('.ico-threadCollapse').removeClass('ico-threadCollapse').addClass('ico-threadOpen');
	},

	slideDown: function() {
		$(this.el).find('.tree_thread > ul > li:not(:first-child)').slideDown('100');
		$(this.el).find('.ico-threadOpen').removeClass('ico-threadOpen').addClass('ico-threadCollapse');
	},

	hide: function() {
		$(this.el).find('.tree_thread > ul > li:not(:first-child)').hide();
		$(this.el).find('.ico-threadCollapse').removeClass('ico-threadCollapse').addClass('ico-threadOpen');
	}
});

var threads = new ThreadCollection;
threads.fetch();

$('.thread_box').each(function(element) {
	var threadId = parseInt($(this).attr('data-id'));
	if (!threads.get(threadId)) {
		threads.add(new ThreadModel({
			id: threadId
		}));
	}
	new ThreadView({
		el: $(this),
		model: threads.get(threadId)
	});
});

var threadLines = new ThreadLineCollection;
$('.thread_line').each(function(element) {
	var threadLineId = parseInt($(this).attr('data-id'));
	threadLines.add(new ThreadLineModel({
		id: threadLineId
	}));
	new ThreadLineView({
		el: $(this),
		model: threadLines.get(threadLineId),
		isAlwaysShownInline: User_Settings_user_show_inline
	});
});

//	})(jQuery);

/**
 * App
 */

var AppView = Backbone.View.extend({
	el: $('body'),

	events: {
		'click #showLoginForm': 'showLoginForm',
		'focus #EntrySearchTerm': 'widenSearchField'
	},

	/**
	 * Widen search field
	 */
	widenSearchField: function(event) {
		var width = 350;
		event.preventDefault();
		if ($(event.currentTarget).width() < width) {
			$(event.currentTarget).animate({
				width: width + 'px'
			},
			"fast"
			);
		}
	},

	showLoginForm: function(event) {
		if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))) {
			return;
		}

		event.preventDefault();
		$('#modalLoginDialog').height('auto');
		var title= event.currentTarget.title;
		$('#modalLoginDialog').dialog({
			modal: true,
			title: title,
			width: 420,
			show: 'fade',
			hide: 'fade',
			position: ['center', 120]
		});
	}
});

var App = new AppView;