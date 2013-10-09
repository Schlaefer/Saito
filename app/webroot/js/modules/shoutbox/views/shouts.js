define([
  'jquery', 'underscore', 'backbone', 'marionette',
  'modules/shoutbox/views/shout'
], function($, _, Backbone, Marionette, ShoutView) {

  "use strict";

  var ShoutboxCollectionView = Marionette.CollectionView.extend({

    itemView: ShoutView,

    itemViewOptions: {},

    conversationCoolOff: 300,
    previousItemTime: null,

    initialize: function(options) {
      this.itemViewOptions.webroot = options.webroot;
    },

    onBeforeRender: function() {
      this.$el.html('');
    },

    onBeforeItemAdded: function(itemView) {
      var itemTime = Date.toUnix(itemView.model.get('time'));
      this.itemTime = itemTime;

      if (this.previousItemTime === null) {
        this.previousItemTime = itemTime;
        return;
      }
      if ((this.previousItemTime - itemTime) > this.conversationCoolOff) {
        this.$el.append('<div class="info_text">' + this.formatTime(this.previousItemTime) + '</div>');
      } else {
        this.$el.append('<hr>');
      }
      this.previousItemTime = itemTime;
    },

    // @todo
    formatTime: function(unixTimestamp) {
      var date = new Date(unixTimestamp * 1000);
      var pad = "00";
      // @todo +2
      var hours = '' + (date.getHours() + 2);
      hours = pad.substring(0, pad.length - hours.length) + hours;
      var minutes = '' + date.getMinutes();
      minutes = pad.substring(0, pad.length - minutes.length) + minutes;
      return  hours + ':' + minutes;
    },

    onRender: function() {
      this.previousItemTime = null;
      this.$el.append('<div class="info_text">' + this.formatTime(this.itemTime) + '</div>');
    }

  });

  return ShoutboxCollectionView;
});
