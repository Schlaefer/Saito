define([
  'jquery', 'underscore', 'backbone', 'marionette', 'models/app',
  'modules/shoutbox/models/control',
  'text!modules/shoutbox/templates/control.html'
], function($, _, Backbone, Marionette, App, SCM, Tpl) {

  "use strict";

  var ShoutboxView = Marionette.ItemView.extend({
    template: _.template(Tpl),
    events: {
      'click #shoutbox-notify': 'onChangeNotify'
    },

    initialize: function() {
      this.model = SCM;
    },

    onRender: function() {
      this._putNotifyCheckbox();
    },

    _putNotifyCheckbox: function() {
      var active = App.reqres.request('app:html5-notification:available');
      if (active !== true) { return; }
      this.notify = this.$('#shoutbox-notify');
      if (this.model.get('notify')) {
        this.notify.attr('checked', 'checked');
      } else {
        this.notify.removeAttr('checked');
      }
      this.notify.show();
    },

    onChangeNotify: function() {
      var isChecked = this.notify.is(':checked');
      this.model.set('notify', isChecked);
      if (isChecked) {
        App.commands.execute('app:html5-notification:activate');
      }
    }

  });

  return ShoutboxView;
});
