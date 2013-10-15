define([
  'jquery', 'underscore', 'backbone', 'marionette', 'moment', 'models/app',
  'modules/shoutbox/views/shout',
  'modules/shoutbox/models/control'
], function($, _, Backbone, Marionette, moment, App, ShoutView, SbCM) {

  "use strict";

  var ShoutboxCollectionView = Marionette.CollectionView.extend({
    itemView: ShoutView,
    itemViewOptions: {},

    /**
     * Sends notification and stores it in ShoutboxControlModel
     */
    _Notifications: {
      _last: 0,
      _models: [],
      _isEnabled: false,

      init: function(options) {
        this._currentUserId = options.currentUserId;
        this._isEnabled = options.isEnabled;
        // mark all existing as read
        this._last = options.last;
      },

      add: function(model) {
        if (this._isEnabled !== true) { return; }
        // user's own shout
        if (this._currentUserId === model.get('user_id')) { return; }
        if (model.get('id') <= this._last) { return; }
        this._models.push(model);
      },

      send: function() {
        if (this._models.length === 0) { return; }

        _.each(this._models, function(model) {
          App.eventBus.trigger('html5-notification', {
            title: model.get('user_name'),
            message: model.get('text')
          });
        });
        this._last = _.first(this._models).get('id');
        this._models = [];
      }
    },

    /**
     * Appends timestamp and/or <hr> to shout
     */
    _Delimiter: {
      _conversationCoolOff: 300,
      _previousItemTime: null,
      tpl: _.template('<div class="info_text"><span title="<%= time_long %>"><%= time %></span></div>'),

      init: function(options) {
        this.$el = options.$el;
      },

      append: function(itemView) {
        var itemTime = moment(itemView.model.get('time'));
        this._itemTime = itemTime;
        // first entry
        if (this._previousItemTime === null) {
          this._previousItemTime = itemTime;
          return;
        }
        if ((this._previousItemTime.unix() - itemTime.unix()) > this._conversationCoolOff) {
          this._appendTimestamp(this._previousItemTime);
        } else {
          this.$el.append('<hr>');
        }
        this._previousItemTime = itemTime;
      },

      finish: function() {
        this._previousItemTime = null;
        if (this._itemTime) {
          this._appendTimestamp(this._itemTime);
        }
      },

      _appendTimestamp: function(time) {
        this.$el.append(this.tpl({
          time: time.format('LT'),
          time_long: time.format('llll')
        }));
      }
    },

    initialize: function(options) {
      this.itemViewOptions.webroot = options.webroot;
      this._Delimiter.init({$el: this.$el});
      // setup Notifications
      this.setupNotifications();
      this.listenTo(SbCM, 'change:notify', this.setupNotifications);
    },

    setupNotifications: function() {
      var last = 0;
      if (this.collection.size() > 0) {
        last = this.collection.first().get('id');
      }
      this._Notifications.init({
        currentUserId: App.currentUser.get('id'),
        isEnabled: SbCM.get('notify'),
        last: last
      });
    },

    onBeforeRender: function() {
      this.$el.html('');
    },

    onBeforeItemAdded: function(itemView) {
      this._Delimiter.append(itemView);
    },

    onAfterItemAdded: function(itemView) {
      this._Notifications.add(itemView.model);
    },

    onRender: function() {
      this._Delimiter.finish();
      this._Notifications.send();
    }

  });

  return ShoutboxCollectionView;
});
