define([
  'jquery', 'underscore', 'backbone', 'marionette',
  'models/app',
  'modules/shoutbox/models/control',
  'templateHelpers',
  'text!modules/shoutbox/templates/shout.html'
], function($, _, Backbone, Marionette, App, SbCM, TemplateHelpers, Tpl) {

  "use strict";

  var ShoutboxView = Marionette.ItemView.extend({

    className: 'shout',

    templateHelpers: TemplateHelpers,

    initialize: function(options) {
      this.webroot = options.webroot;

      this.listenTo(SbCM, 'change:mar', this.render);
    },

    serializeData: function() {
      var data = this.model.toJSON(),
          _isNew = this.model.get('id') > SbCM.get('mar'),
          _isOwn = this.model.get('user_id') === App.currentUser.get('id');
      if (_isOwn) {
        this.$el.addClass('shoutbox-shout-cu');
      }
      if (_isNew && !_isOwn) {
        this.$el.addClass('shoutbox-shout-new');
      } else {
        this.$el.addClass('shoutbox-shout-old');
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
