$(document).ready(function() {
	(function ($) {

	var Thread = Backbone.Model.extend({

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
		model: Thread,
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
			threads.add(new Thread({
				id: threadId
			}));
	var SearchFieldView = Backbone.View.extend({
		events: {
			'focus': 'widen'
		},
		widen: function(e) {
			var width = 350;
			e.preventDefault();
			if ($(this.el).width() < width) {
				$(this.el).animate({
					width: width + 'px'
				},
				"fast"
				);
			}
		}
		new ThreadView({
			el: $(this),
			model: threads.get(threadId)
		});
	});
})(jQuery);

	new SearchFieldView({
		el: $("#EntrySearchTerm")
	});
});
