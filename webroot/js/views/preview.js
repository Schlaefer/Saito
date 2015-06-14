define([
  'jquery',
  'underscore',
  'marionette',
  'text!templates/spinner.html'
], function($, _, Marionette, spinnerTpl) {

  'use strict';

  var PreviewView = Marionette.ItemView.extend({

    templates: {
      content: _.template('<%= rendered %>'),
      spinner: _.template(spinnerTpl)
    },

    modelEvents: {
      'change:fetchingData': '_fetchingData',
      'change:rendered': 'render'
    },

    _fetchingData: function() {
      if (this.model.get('fetchingData')) {
        this.render();
      }
    },

    getTemplate: function() {
      if (this.model.get('fetchingData')) {
        return this.templates.spinner;
      }
      return this.templates.content;
    }

  });

  return PreviewView;

});
