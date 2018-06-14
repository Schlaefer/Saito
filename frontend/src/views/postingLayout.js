import _ from 'underscore';
import Marionette from 'backbone.marionette';
import EventBus from 'app/vent';
import ActionView from 'views/postingAction';
import { PostingContentView } from 'views/postingContent.ts';
import SliderView from 'views/postingSlider';
import { SpinnerView } from 'views/SpinnerView';

export default Marionette.View.extend({

  initialize: function (options) {
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

  _loadData: function (options) {
    const spinner = (new SpinnerView()).render();
    this.$el.html(spinner.$el);
    this.model.fetchHtml({
      success: _.bind(function () {
        this.$el.html(this.model.get('html'));
        this._dataReady(options);
      }, this)
    });
  },

  _dataReady: function (options) {
    // grabs inline-data from html content
    var _entry = this.$('.js-data').data('entry');
    this.model.set(_entry, { silent: true });

    var contentView = new PostingContentView({
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

    EventBus.vent.trigger('Vent.Posting.View.afterRender', { $el: this.$el });
  }

});
