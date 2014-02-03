define(['underscore', 'marionette',
  'views/postingAction', 'views/postingContent', 'views/postingSlider'],
    function(_, Marionette, ActionView, ContentView, SliderView) {
  'use strict';

  var postingLayout = Marionette.Layout.extend({

    initialize: function(options) {
      _.defaults(options, {
        inline: false,
        parentThreadline: false
      });
      // ensures html/data is loaded and in DOM
      if (options.inline) {
        this.model.fetchHtml();
        this.$el.html(this.model.get('html'));
      }
      // grabs inline-data from html content
      var _entry = this.$('.js-data').data('entry');
      this.model.set(_entry, {silent: true});

      var contentView = new ContentView({
        el: this.$('.postingBody'),
        model: this.model
      });

      var actionsView = new ActionView({
        el: this.$('.panel-footer'),
        model: this.model
      });

      var sliderView = new SliderView({
        el: this.$('.posting_formular_slider'),
        model: this.model,
        collection: this.collection,
        parentThreadline: options.parentThreadline
      });
    }

  });

  return postingLayout;

});