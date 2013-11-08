define([
  'underscore',
  'backbone',
  'models/app',
  'lib/saito/backbone.modelHelper'
], function(_, Backbone, App) {
  "use strict";

  var PostingModel = Backbone.Model.extend({

    defaults: {
      isBookmarked: false,
      isSolves: false,
      isAnsweringFormShown: false,
      html: ''
    },

    initialize: function() {
      this.listenTo(this, 'change:isSolves', this.syncSolved);
      this.listenTo(this, 'change:isBookmarked', this.syncBookmarked);
    },

    isRoot: function() {
      var _pid = this.get('pid');
      if (!_.isNumber(_pid)) {
        throw 'pid is not a number.';
      }
      return _pid === 0;
    },

    syncBookmarked: function() {
      if (!this.get('isBookmarked')) {
        return;
      }
      $.ajax({
        url: App.settings.get('webroot') + "bookmarks/add",
        type: 'POST',
        dataType: 'json',
        data: 'id=' + this.get('id')
      });
    },

    syncSolved: function() {
      $.ajax({
        url: App.settings.get('webroot') + 'entries/solve/' + this.get('id'),
        type: 'POST',
        dateType: 'json'
      });
    },

    fetchHtml: function() {
      $.ajax({
        success: _.bind(function(data) {
          this.set('html', data);
        }, this),
        type: 'POST',
        async: false,
        dateType: "html",
        url: App.settings.get('webroot') + 'entries/view/' + this.get('id')
      });
    }

  });

  return PostingModel;
});