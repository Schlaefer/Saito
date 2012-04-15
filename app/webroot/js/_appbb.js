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
			"click .btn-threadCollapse":  "collapseThread",
			"click .btn-threadOpen":  		"collapseThread"
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
			$(this.el).find('.btn-threadCollapse').removeClass('btn-threadCollapse').addClass('btn-threadOpen');
		},

		slideDown: function() {
			$(this.el).find('.tree_thread > ul > li:not(:first-child)').slideDown('100');
			$(this.el).find('.btn-threadOpen').removeClass('btn-threadOpen').addClass('btn-threadCollapse');
		},

		hide: function() {
			$(this.el).find('.tree_thread > ul > li:not(:first-child)').hide();
			$(this.el).find('.btn-threadCollapse').removeClass('btn-threadCollapse').addClass('btn-threadOpen');
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
		}
		new ThreadView({
			el: $(this),
			model: threads.get(threadId)
		});
	});
})(jQuery);

