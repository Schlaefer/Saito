import $ from 'jquery';
import _ from 'underscore';
import Marionette from 'backbone.marionette';
import spinnerTpl from 'templates/spinner.html';

export default Marionette.View.extend({

  templates: {
    content: _.template('<%= rendered %>'),
    spinner: spinnerTpl,
  },

  modelEvents: {
    'change:fetchingData': '_fetchingData',
    'change:rendered': 'render'
  },

  _fetchingData: function () {
    if (this.model.get('fetchingData')) {
      this.render();
    }
  },

  getTemplate: function () {
    if (this.model.get('fetchingData')) {
      return this.templates.spinner;
    }
    return this.templates.content;
  }

});
