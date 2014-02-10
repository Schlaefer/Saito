define([
  'jquery',
  'underscore',
  'marionette',
  'text!templates/spinner.html'
], function($, _, Marionette, spinnerTpl) {

  "use strict";

  var PreviewView = Backbone.View.extend({

    initialize: function() {
      this.render();

      this.listenTo(this.model, "change:fetchingData", this._spinner);
      this.listenTo(this.model, "change:rendered", this.render);
    },

    _spinner: function(model) {
      if (model.get('fetchingData')) {
        this.$el.html(spinnerTpl);
      } else {
        this.$el.html('');
      }
    },

    render: function() {
      var rendered;
      rendered = this.model.get('rendered');
      if (!rendered) {
        rendered = '';
      }
      this.$el.html(rendered);
      return this;
    }

  });

  return PreviewView;

});
