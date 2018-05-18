import $ from 'jquery';
import _ from 'underscore';
import Marionette from 'backbone.marionette';
import GeshisCollection from 'collections/geshis';
import GeshiView from 'views/geshi';

export default Marionette.View.extend({

  initialize: function () {
    this.listenTo(this.model, 'change:isAnsweringFormShown', this._toggleAnsweringForm);
    this.listenTo(this.model, 'change:html', this.render);

    // init form/elements for entries/view when $el is already there
    this._initGeshi('.geshi-wrapper');
  },

  _toggleAnsweringForm: function () {
    if (this.model.get('isAnsweringFormShown')) {
      this._hideSignature();
    } else {
      this._showSignature();
    }
  },

  _showSignature: function () {
    this.$('.postingBody-signature').slideDown('fast');
  },

  _hideSignature: function () {
    this.$('.postingBody-signature').slideUp('fast');
  },

  _initGeshi: function (element_n) {
    var geshi_elements = this.$(element_n);
    if (geshi_elements.length > 0) {
      var geshis = new GeshisCollection();
      geshi_elements.each(function (key, element) {
        var view = new GeshiView({ el: element, collection: geshis });
      });
    }
  },

  render: function () {
    this.$el.html(this.model.get('html'));
    this._initGeshi('.geshi-wrapper');
    return this;
  }

});
