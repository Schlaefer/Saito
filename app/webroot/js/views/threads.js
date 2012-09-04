define([
	'jquery',
	'underscore',
	'backbone',
	], function($, _, Backbone) {

		var ThreadView = Backbone.View.extend({

			className: 'thread_box',

			events: {
				"click .btn-threadCollapse":  "collapseThread",
				"click .js-btn-openAllThreadlines": "openAllThreadlines",
				"click .js-btn-closeAllThreadlines": "closeAllThreadlines",
				"click .js-btn-showAllNewThreadlines": "showAllNewThreadlines"
			},

			initialize: function(){
				this.model.on('change:isThreadCollapsed', this.toggleCollapseThread, this);

				if (this.model.get('isThreadCollapsed')) {
					this.hide();
				}
				this.initHover();
			},

			/**
			 * highlight for Toolbar
			 */
			initHover: function() {
				this.$el.hoverIntent(
					_.bind(function () {
						$('.thread_tools', this.$el).delay(50).fadeTo(200, 1) ;
					}, this),
					_.bind(function () {
						$('.thread_tools', this.$el).delay(400).fadeTo(1000, 0.2);
					}, this)
					);
			},

			/**
			 * Opens all threadlines
			 */
			openAllThreadlines: function(event) {
				event.preventDefault();
				_.each(
					this.model.threadlines.where({
						isInlineOpened: false
					}), function(model) {
						model.set({
							isInlineOpened: true
						})
					}, this);

			},

			/**
			 * Closes all threadlines
			 */
			closeAllThreadlines: function(event) {
				event.preventDefault();
				_.each(
					this.model.threadlines.where({
						isInlineOpened: true
					}), function(model) {
						model.set({
							isInlineOpened: false
						})
					}, this);
			},

			/**
			 * Toggles all threads marked as unread/new in a thread tree
			 */
			showAllNewThreadlines: function(event) {
				event.preventDefault();
				_.each(
					this.model.threadlines.where({
						isInlineOpened: false,
						isNewToUser: true
					}), function(model) {
						model.set({
							isInlineOpened: true
						})
					}, this);
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

		return ThreadView;

	});