define([
  'jquery', 'underscore', 'backbone', 'marionette',
  'models/app',
  'modules/shoutbox/models/control',
  'text!modules/shoutbox/templates/shout.html'
], function($, _, Backbone, Marionette, App, SbCM, Tpl) {

  "use strict";

  var ShoutboxView = Marionette.ItemView.extend({

    className: 'shout',

    initialize: function(options) {
      this.webroot = options.webroot;

      this.listenTo(SbCM, 'change:mar', this.render);
    },

    serializeData: function() {
      var data = this.model.toJSON();
      data.user_url = this.webroot + 'users/view/' +
          this.model.get('user_id');

      var _isNew = this.model.get('id') > SbCM.get('mar'),
          _isOwn = this.model.get('user_id') === App.currentUser.get('id');
      if (_isNew && !_isOwn) {
        data.icon = 'fa-comment';
      } else {
        data.icon = 'fa-comment-o';
      }
      return data;
    },

    onRender: function() {
      SbCM.setLastId(this.model.get('id'));
    },

    template: function(data) {
      return _.template(Tpl, data);
    }

  });

  return ShoutboxView;
});
