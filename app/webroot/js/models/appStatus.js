define([
  'underscore',
  'backbone',
  'cakeRest',
  'app/vent'
], function(_, Backbone, cakeRest, EventBus) {

  "use strict";

  var AppStatusModel = Backbone.Model.extend({

    initialize: function() {
      this.methodToCakePhpUrl = _.clone(this.methodToCakePhpUrl);
      this.methodToCakePhpUrl.read = 'status/';

      this.listenTo(this, 'change:lastShoutId', this.onNewShout);
    },

    onNewShout: function(model) {
      var id = model.get('lastShoutId');
      EventBus.commands.execute('shoutbox:update', id);
    },

    setWebroot: function(webroot) {
      this.webroot = webroot + 'saitos/';
    }

  });

  _.extend(AppStatusModel.prototype, cakeRest);

  return AppStatusModel;

});
