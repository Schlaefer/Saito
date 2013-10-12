define([
  'jquery', 'underscore', 'backbone', 'marionette', 'moment',
  'modules/shoutbox/views/shout'
], function($, _, Backbone, Marionette, moment, ShoutView) {

  "use strict";

  var ShoutboxCollectionView = Marionette.CollectionView.extend({

    itemView: ShoutView,

    itemViewOptions: {},

    tpl: _.template('<div class="info_text"><span title="<%= time_long %>"><%= time %></span></div>'),

    conversationCoolOff: 300,
    previousItemTime: null,

    initialize: function(options) {
      this.itemViewOptions.webroot = options.webroot;
    },

    onBeforeRender: function() {
      this.$el.html('');
    },

    onBeforeItemAdded: function(itemView) {
      var itemTime = moment(itemView.model.get('time'));
      this.itemTime = itemTime;
      if (this.previousItemTime === null) {
        this.previousItemTime = itemTime;
        return;
      }
      if ((this.previousItemTime.unix() - itemTime.unix()) > this.conversationCoolOff) {
        this.appendTimestamp(this.previousItemTime);
      } else {
        this.$el.append('<hr>');
      }
      this.previousItemTime = itemTime;
    },

    appendTimestamp: function(time) {
      this.$el.append(this.tpl({
        time: time.format('LT'),
        time_long: time.format('llll')
      }));
    },

    onRender: function() {
      this.previousItemTime = null;
      if (this.itemTime) {
        this.appendTimestamp(this.itemTime);
      }
    }

  });

  return ShoutboxCollectionView;
});
