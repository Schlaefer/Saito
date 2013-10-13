define(['underscore', 'backbone'], function(_, Backbone) {
  "use strict";

  var ShoutboxControlModel = Backbone.Model.extend({
    defaults: {
      notify: false
    },

    initialize: function() {
      this.restoreNotify();

      this.listenTo(this, 'change:notify', this.saveNotify);
    },

    restoreNotify: function() {
      if ('localStorage' in window) {
        this.set('notify', localStorage.getItem('shoutbox-notify') === "true");
      }
    },

    saveNotify: function() {
      if ('localStorage' in window) {
        localStorage.setItem('shoutbox-notify', this.get('notify'));
      }
    }
  });

  return new ShoutboxControlModel();
});
