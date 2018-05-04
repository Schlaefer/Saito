define([
  'jquery',
  'underscore',
  'backbone',
  'models/app',
  'text!templates/upload.html',
  'lib/saito/jquery.insertAtCaret'
], function($, _, Backbone, App, uploadTpl) {

  "use strict";

  var UploadView = Backbone.View.extend({

    className: "panel-content upload_box current",

    events: {
      "click .upload_box_delete": "_removeUpload",
      "click .btn-primary": "_insert"
    },

    initialize: function(options) {
      this.textarea = options.textarea;

      this.listenTo(this.model, "destroy", this._uploadRemoved);
    },

    _removeUpload: function(event) {
      event.preventDefault();
      this.model.destroy({
            success: _.bind(function(model, response) {
              App.eventBus.trigger(
                  'notification',
                  response
              );
            }, this)
          }
      );
    },

    _uploadRemoved: function() {
      this.remove();
    },

    _insert: function(event) {
      event.preventDefault();
      $(this.textarea).insertAtCaret(
          "[upload]" + this.model.get('name') + "[/upload]");
    },

    render: function() {
      this.$el.html(_.template(uploadTpl, this.model.toJSON()));
      return this;
    }

  });

  return UploadView;

});
