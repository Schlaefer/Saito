define([
    'underscore',
    'backbone'
], function (_, Backbone) {

    "use strict";

    var CurrentUserModel = Backbone.Model.extend({

      isLoggedIn: function() {
        return this.get('id') > 0;
      }

    });

    return CurrentUserModel;
});
