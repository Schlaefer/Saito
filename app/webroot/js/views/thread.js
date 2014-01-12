define([
  'jquery',
  'underscore',
  'backbone',
  'models/app',
  'collections/threadlines', 'views/threadlines'
], function($, _, Backbone, App, ThreadLinesCollection, ThreadLineView) {

  'use strict';

  var ThreadView = Backbone.View.extend({

    className: 'threadBox',

    events: {
      "click .btn-threadCollapse": "collapseThread",
      "click .js-btn-openAllThreadlines": "openAllThreadlines",
      "click .js-btn-closeAllThreadlines": "closeAllThreadlines",
      "click .js-btn-showAllNewThreadlines": "showAllNewThreadlines"
    },

    initialize: function(options) {
      this.postings = options.postings;

      this.$rootUl = this.$('ul.root');
      this.$subThreadRootIl = $(this.$rootUl.find('li:not(:first-child)')[0]);

      if (this.model.get('isThreadCollapsed')) {
        this.hide();
      } else {
        this.show();
      }

      if (!App.reqres.request('app:localStorage:available')) {
        this._hideCollapseButton();
      }

      this.listenTo(App.eventBus, 'newEntry', this._showNewThreadLine);
      this.listenTo(this.model, 'change:isThreadCollapsed', this.toggleCollapseThread);
    },

    _showNewThreadLine: function(options) {
      var threadLine;
      // only append to the id it belongs to
      if (options.tid !== this.model.get('id')) { return; }
      threadLine = new ThreadLineView({
        id: options.id,
        collection: this.model.threadlines,
        postings: this.postings
      });
      this._appendThreadlineToThread(options.pid, threadLine.render().$el);
    },

    _appendThreadlineToThread: function(pid, $el) {
      var parent,
          existingSubthread;
      parent = this.$('.js-thread_line[data-id="' + pid + '"]');
      existingSubthread = (parent.next().not('.js_threadline').find('ul:first'));
      if (existingSubthread.length === 0) {
        $el.wrap("<ul class=\"threadTree-node\"></ul>").parent().wrap("<li></li>").parent().insertAfter(parent);
      } else {
        existingSubthread.append($el);
      }
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
              isInlineOpened: true,
              shouldScrollOnInlineOpen: false
            });
          }, this);

    },

    /**
     * Closes all threadlines
     */
    closeAllThreadlines: function(event) {
      if (event) {
        event.preventDefault();
      }
      _.each(
          this.model.threadlines.where({
            isInlineOpened: true
          }), function(model) {
            model.set({
              isInlineOpened: false
            });
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
              isInlineOpened: true,
              shouldScrollOnInlineOpen: false
            });
          }, this);
    },

    _hideCollapseButton: function() {
      this.$('.btn-threadCollapse').css('visibility', 'hidden');
    },

    collapseThread: function(event) {
      event.preventDefault();
      this.closeAllThreadlines();
      this.model.toggle('isThreadCollapsed');
      this.model.save();
    },

    toggleCollapseThread: function(model, isThreadCollapsed) {
      if (isThreadCollapsed) {
        this.slideUp();
      } else {
        this.slideDown();
      }
    },

    slideUp: function() {
      this.$subThreadRootIl.slideUp(300);
      this.markHidden();
    },

    slideDown: function() {
      this.$subThreadRootIl.slideDown(300);
      this.markShown();
//				$(this.el).find('.ico-threadOpen').removeClass('ico-threadOpen').addClass('ico-threadCollapse');
//				$(this.el).find('.btn-threadCollapse').html(this.l18n_threadCollapse);
    },

    hide: function() {
      this.$subThreadRootIl.hide();
      this.markHidden();
    },

    show: function() {
      this.$subThreadRootIl.show();
      this.markShown();
    },

    markShown: function() {
      $(this.el).find('.fa-thread-closed').removeClass('fa-thread-closed').addClass('fa-thread-open');
    },

    markHidden: function() {
      $(this.el).find('.fa-thread-open').removeClass('fa-thread-open').addClass('fa-thread-closed');
      // this.l18n_threadCollapse = $(this.el).find('.btn-threadCollapse').html();
      // $(this.el).find('.btn-threadCollapse').prepend('&bull;');
    }

  });

  return ThreadView;

});