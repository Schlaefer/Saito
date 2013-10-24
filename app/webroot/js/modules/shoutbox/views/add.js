define([
  'jquery', 'underscore', 'backbone', 'marionette', 'models/app',
  'modules/shoutbox/models/shout',
  'text!modules/shoutbox/templates/add.html',
  'jqueryAutosize'
], function($, _, Backbone, Marionette, App, ShoutModel, Tpl) {

  "use strict";

  var ShoutboxAdd = Marionette.ItemView.extend({

    template: _.template(Tpl),

    events: {
      "keyup form": "formUp",
      "keydown form": "formDown"
    },

    submit: function() {
      this.model.save(
          {text: this.textarea.val()},
          {
            success: _.bind(function(model, response) {
              // update view with latest data coming as answer from the add request
              this.collection.reset(response);
            }, this)
          }
      );
    },

    clearForm: function() {
      this.textarea.val('').trigger('autosize');
    },

    formDown: function(event) {
      if (event.keyCode === 13 && event.shiftKey === false) {
        event.preventDefault();
        this.submit();
        this.clearForm();
      }
    },

    formUp: function() {
      if (this.textarea.val().length > 0) {
        App.eventBus.trigger('breakAutoreload');
      } else if (this.textarea.val().length === 0) {
        App.eventBus.trigger('initAutoreload');
      }
    },

    onShow: function() {
      this.textarea = this.$('#shoutbox-input');
      this.textarea.autosize();
    }

  });

  return ShoutboxAdd;

});
