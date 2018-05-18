import _ from 'underscore';
import $ from 'jquery';
import Marionette from 'backbone.marionette';
import App from 'models/app';

export default Marionette.View.extend({

  _warningTpl: _.template('<div class="app-prerequisites-warning"> <%- warning %> </div>'),

  initialize: function() {
    this._testLocalStorage();
  },

  _testLocalStorage: function() {
    if (!App.eventBus.request('app:localStorage:available')) {
      this._addWarning($.i18n.__('This web-application depends on Cookies and localStorage. Please make those available in your browser.'));
    }
  },

  _addWarning: function(warning) {
    this.$el.append(this._warningTpl({warning: warning}));
  }

});
