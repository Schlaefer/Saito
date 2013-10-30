define([
  'underscore',
  'backbone',
  'models/app',
  'lib/saito/backbone.modelHelper'
], function(_, Backbone, App) {

    "use strict";

    var PostingModel = Backbone.Model.extend({

        defaults: {
          isSolved: false,
          isAnsweringFormShown: false,
          html: ''
        },

        initialize: function() {
          this.listenTo(this, 'change:isSolved', this.syncSolved);
        },

        syncSolved: function() {
          $.ajax({
            type: 'post',
            dateType: 'json',
            url: App.settings.get('webroot') + 'entries/solve/' + this.get('id')
          });
        },

        fetchHtml: function() {

          $.ajax({
            success: _.bind(function(data) {
              this.set('html', data);
            }, this),
            type: "post",
            async: false,
            dateType: "html",
            url: App.settings.get('webroot') + 'entries/view/' + this.get('id')
          });
        }

      });

  return PostingModel;
});