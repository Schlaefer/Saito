import Marionette from 'backbone.marionette';
import App from 'models/app';
import { AnsweringView } from 'modules/answering/answering.ts';
import { SpinnerView } from 'views/SpinnerView';
import * as _ from 'underscore';

export default Marionette.View.extend({

  answeringForm: false,

  ui: {
    btnClose: '.js-btnAnsweringClose',
  },

  events: {
    'click @ui.btnClose': 'onBtnClose'
  },

  template: _.noop,

  initialize: function (options) {
    this.parentThreadline = options.parentThreadline || null;

    this.listenTo(this.model, 'change:isAnsweringFormShown', this.toggleAnsweringForm);
  },

  onBtnClose: function (event) {
    event.preventDefault();
    this.model.set('isAnsweringFormShown', false);
  },

  toggleAnsweringForm: function () {
    if (this.model.get('isAnsweringFormShown')) {
      this._hideAllAnsweringForms();
      this._showAnsweringForm();
    } else {
      this._hideAnsweringForm();
    }
  },

  _showAnsweringForm: function () {
    App.eventBus.trigger('breakAutoreload');
    if (this.answeringForm === false) {
      const spinner = (new SpinnerView()).render();
      this.$el.html(spinner.$el);
    }
    this.$el.slideDown('fast');
    if (this.answeringForm === false) {
      this.answeringForm = new AnsweringView({
        el: this.$el,
        model: this.model,
        parentThreadline: this.parentThreadline
      });
      this.answeringForm.render();
    }
  },

  _hideAnsweringForm: function () {
    this.$el.slideUp('fast', function () {
      App.eventBus.trigger('change:DOM');
    });
  },

  _hideAllAnsweringForms: function () {
    // we have #id problems with more than one markItUp on a page
    this.collection.forEach(function (posting) {
      if (posting.get('id') !== this.model.get('id')) {
        posting.set('isAnsweringFormShown', false);
      }
    }, this);
  }

});
