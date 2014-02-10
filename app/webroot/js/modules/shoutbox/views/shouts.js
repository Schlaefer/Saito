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
     * Puts timestamp or <hr> between shouts
     */
    _Delimiter: {
      _conversationCoolOff: 300,
      _previousItemTime: null,
      tpl: _.template('<div class="infoText"><span title="<%= time_long %>"><%= time %></span></div>'),

      append: function(itemView) {
        var itemTime = moment(itemView.model.get('time'));
        // first entry
        if (this._previousItemTime === null) {
          this._previousItemTime = itemTime;
          return;
        }
        this._itemTime = itemTime;
        this.$el = itemView.$el;
        if ((this._previousItemTime.unix() - itemTime.unix()) > this._conversationCoolOff) {
          this._prepend(this._previousItemTime);
        } else {
          this._prepend('<hr>');
        }
        this._previousItemTime = itemTime;
      },

      finish: function() {
        this._previousItemTime = null;
        if (this._itemTime) {
          this.$el.after(this._formatTime(this._itemTime));
        }
      },

      _prepend: function(time) {
        this.$el.before(this._formatTime(time));
      },

      _formatTime: function(time) {
        var out = time;
        // time is Moment object
        if (_.isObject(time)) {
          out = this.tpl({
            time: time.format('LT'),
            time_long: time.format('llll')
          });
        }
        return out;
      }
    },

    initialize: function(options) {
      this.itemViewOptions.webroot = options.webroot;
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

    onAfterItemAdded: function(itemView) {
      this._Delimiter.append(itemView);
      this._Notifications.add(itemView.model);
    },

    onRender: function() {
      this._Delimiter.finish();
      this._Notifications.send();
    }

  });

  return ShoutboxCollectionView;
});
