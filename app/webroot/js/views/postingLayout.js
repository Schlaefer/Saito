define(['underscore', 'marionette', 'app/app',
  'views/postingAction', 'views/postingContent', 'views/postingSlider',
  'text!templates/spinner.html'],
    function(_, Marionette, App, ActionView, ContentView, SliderView, spinnerTpl) {
  'use strict';

  var postingLayout = Marionette.Layout.extend({

    initialize: function(options) {
      _.defaults(options, {
        inline: false,
        parentThreadline: false
      });
      // ensures html/data is loaded and in DOM
      if (options.inline) {
        this._loadData(options);
      } else {
        this._dataReady(options);
      }
    },

    _loadData: function(options) {
      this.$el.html(spinnerTpl);
      this.model.fetchHtml({
        success: _.bind(function() {
          this.$el.html(this.model.get('html'));
          this._dataReady(options);
        }, this)
      });
    },

    _dataReady: function(options) {
      // grabs inline-data from html content
      var _entry = this.$('.js-data').data('entry');
      this.model.set(_entry, {silent: true});

      var contentView = new ContentView({
        el: this.$('.postingBody'),
        model: this.model
      });

      var actionsView = new ActionView({
        el: this.$('.postingLayout-actions'),
        model: this.model
      });

      var sliderView = new SliderView({
        el: this.$('.postingLayout-slider'),
        model: this.model,
        collection: this.collection,
        parentThreadline: options.parentThreadline
      });

      App.vent.trigger('Vent.Posting.View.afterRender', {$el: this.$el});
    }

  });

  return postingLayout;

});